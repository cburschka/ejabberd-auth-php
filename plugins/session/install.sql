CREATE TABLE `{TAB}` (
	username VARCHAR(255),
	secret VARCHAR(40),
	created INT,
	PRIMARY KEY(username, secret),
	INDEX(created)
);
