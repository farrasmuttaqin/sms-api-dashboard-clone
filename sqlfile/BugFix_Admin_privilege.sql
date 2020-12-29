/*49104 Fixxing issue User as Admin on Client cannot create User on SMS API Dashboard */;
/*49220 Solving Client System Admin cannot view other users in their company */;

INSERT INTO SMSAPI_DASHBOARD.AD_PRIVILEGE_ROLE(privilege_id,role_id) VALUES(2,2);

/*the added privilege is user.acc.company for Allow the user to access users data in his company (ADMIN Privilege)*/;

