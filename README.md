# DatabaseCompare
Database Compare using LDAP and SQL: (idea from my job at IT) Summer 2017

Description: 
- This project was created with PHP, SQL, and LDAP functions.
- It takes two sets of data, one with accurate data and another with out of date data, finds the elements that are out of date and updates the out of date data to be accurate.

Process:
- I derived a list of users from Active Directory on a Windows 2008 Server using LDAP functions in PHP.
- Then, I used SQL to derive a second list of users from a Microsoft Access database.
- Next, I compared the outdated information from the LDAP query with the up to date information from the SQL query to then modify and update the users in Active Directory.

Difficulties Faced: 
- I had a lot of problems setting up the development environment, i.e. setting up the server on a virtual machine so that I could communicate to it from my computer.
  - Solution: I was able to get past this issue by doing my research on virtual machines and then using my Networking teacher as a resource for any extra questions I had. 

New Skills Acquired:
- I had to learn how to connect to Active Directory by connecting, adjusting LDAP options, and then binding to Active Directory.
- I had to learn how objects are stored in Active Directory in order to know how to change the location of each object to the correct OU.
- I had to learn how to create classes in PHP. 
