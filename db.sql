CREATE TABLE Admin (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    login varchar(5) NOT NULL,
    hash_pass varchar(32) NOT NULL,
    PRIMARY KEY (id)
);
INSERT INTO Admin (login, hash_pass) VALUES ('admin','21232f297a57a5a743894a0e4a801fc3');