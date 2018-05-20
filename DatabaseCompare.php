
<?php
    class DatabaseCompare
    {
        private $LDAPelements = [];
        private $SQLelements = [];
        private $matchingElements = [];
        private $nonMatchingElements = [];
        private $copyElementsInLDAP = [];
        private $ldapDN;
        private $ldapElementFilter;
        private $sqlID;
        private $sqlQuery;
        private $oldDNLocation;
        private $RDNcn;
        private $newDN;
        private $ds;
        private $LDAPusername;
        private $LDAPpassword;
        private $SQLusername;
        private $SQLpassword;
        private $odbcDSN;
        private $SQLdb;

        public function __construct()
        {
            $this->ldapDN = "ou=Active,ou=Students,dc=qserver,dc=com";
            $this->ldapElementFilter = ("cn=*");
            $this->sqlID = "StudentName";
            $this->sqlQuery = "SELECT " . $this->sqlID . " FROM TestDBTable";
            $this->newDN = 'ou=Inactive,ou=Students,dc=qserver,dc=com';

            $this->LDAPusername = '';
            $this->LDAPpassword = '';
            $this->SQLusername = "";
            $this->SQLpassword = "";
            $this->odbcDSN = "TestDB";
        }

        private function RunConnections()
        {
            $this->LDAPConnectBind();
            $this->dbOpen();
        }

        private function LDAPConnectBind()
        {
            echo "<h3>LDAP query</h3>";
            echo "connecting . . . ";

            $this->ds = ldap_connect("qserver.com");

            echo "connection result is " . $this->ds . "<br />";
            if ($this->ds)
            {
	        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	        ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);

                echo "Binding . . .";
                $result = ldap_bind($this->ds, $this->LDAPusername, $this->LDAPpassword);
                echo "The bind result is " . $result . "<br />";

                if ($result)
                {
                    echo "LDAP bind successful.<br />";
                }
                else
                {
                    echo "LDAP bind unsuccessful.<br />";
                }
            }
            else
                echo "<h4>Unable to connect to LDAP server</h4>";
        }

        private function dbOpen()
        {
            try {
             $this->SQLdb = new PDO("odbc:$this->odbcDSN","$this->SQLusername","$this->SQLpassword");
             $this->SQLdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e) {
            die("[ERROR: " . $e->getMessage() . "]");
            }
        }

        private function FillAndCompare()
        {
            $this->GetLDAPElements();
            sort($this->LDAPelements);
            $this->GetSQLElements();
            sort($this->SQLelements);
            $this->CompareArrays();
        }

        private function GetLDAPElements()
        {
            $searchResult = ldap_search($this->ds, $this->ldapDN, $this->ldapElementFilter);
            $LDAPdataraw = ldap_get_entries($this->ds, $searchResult);
            for($count = 0; $count < $LDAPdataraw["count"]; $count++)
                $this->LDAPelements[$count] = $LDAPdataraw[$count]["cn"][0];
        }

        private function GetSQLElements()
        {
            $SQLdata_raw = $this->SQLdb->query($this->SQLquery);
            $SQLdata_raw->setFetchMode(PDO::FETCH_ASSOC);
            $count = 0;
            while ($row = $SQLdata_raw->fetch())
            {
                $this->SQLelements[$count] = $row[$sqlID]; //change StudentName
                $count++;
            }
        }

        private function CompareArrays()
        {
            for($count = 0; $count < sizeof($this->LDAPelements); $count++)
            {
                if(in_array($this->LDAPelements[$count], $this->SQLelements))
                {
                    if($this->CheckForCopyElement($count))
                    {
                        array_push($this->copyElementsInLDAP, $this->LDAPelements[$count]);
                    }
                    else
                    {
                        array_push($this->matchingElements, $this->LDAPelements[$count]);
                    }
                }
                else
                {
                    array_push($this->nonMatchingElements, $this->LDAPelements[$count]);
                }
            }
        }

        private function CheckForCopyElement($index)
        {
            if($input > 0)
	    {
                if ($this->LDAPelements[$index] == $this->LDAPelements[$index - 1])
			return true;
		else
			return false;
	    }
	    else
		return false;
        }

        private function RemoveFromLDAP()
        {
            for($count = 0; $count < sizeof($this->nonMatchingElements); $count++)
            {
                $this->oldDNLocation = $this->nonMatchingElements[$count] . $ldapDN;
                $this->RDNcn = $this->nonMatchingElements[$count];

                ldap_rename($this->ds, $this->oldDNLocation, $this->RDNcn, $this->newDN, TRUE);
            }
        }

        private function RunClose()
        {
            $this->LDAPClose();
            $this->dbClose();
        }

        private function LDAPClose()
        {
            echo "Closing connection";
            ldap_close($this->ds); 
        }

        private function dbClose()
        {
            $this->SQLdb = null;
        }

        public function RunDatabaseCompare()
        {
            $this->RunConnections();
            $this->FillAndCompare();
            $this->RemoveFromLDAP();
            $this->RunClose();
        }
    }

    $Test = new DatabaseCompare();
    $Test->RunDatabaseCompare();

?>
