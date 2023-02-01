CREATE DATABASE IF NOT EXISTS api_rest_proyecto;
USE api_rest_proyecto;

CREATE TABLE users(
id                      int(255) auto_increment not null,
name                    varchar(100) NOT NULL,
surname                 varchar(100) NOT NULL,
role                    varchar(50),
state                   varchar(50),
type                    varchar(50),
email                   varchar(255) NOT NULL,
password                varchar(255) NOT NULL,
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
remember_token          varchar(255),
validation_token        varchar(250),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE animals(
id                      int(255) auto_increment not null,
user_id                 int(255),
nickname                varchar(100),
certification_name      varchar(100),
registration_number     varchar(100),    
birth_weight            int(255),
code                    varchar(100) NOT NULL,
entry_date              datetime DEFAULT NULL,
birth_date              datetime DEFAULT NULL,
death_date              datetime DEFAULT NULL,
sex                     varchar(50) not null,
race                    varchar(50),
animal_state            varchar(100),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_animals PRIMARY KEY(id),
CONSTRAINT fk_animal_user FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

CREATE TABLE images_animals(
id                      int(255) auto_increment not null,
image_name              varchar(100),
title                   varchar(50),
description             text,
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
animal_id               int(255),
CONSTRAINT pk_images_animals PRIMARY KEY(id),
FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;

CREATE TABLE sales(
id                      int(255) auto_increment not null,
sale_type               varchar(100),
weight                  int(255),
price_total             int(255),
price_kg                int(255),
auction_commission      int(255),
auction_name            varchar(100),
description             text,
sale_date               datetime DEFAULT NULL,
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
animal_id               int(255),
CONSTRAINT pk_sales PRIMARY KEY(id),
FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;

CREATE TABLE purchases(
id                      int(255) auto_increment not null,
purchase_type           varchar(100),
weight                  int(255),
price_total             int(255),
price_kg                int(255),
auction_commission      int(255),
auction_name            varchar(100),
description             text,
purchase_date           datetime DEFAULT NULL,
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
animal_id                  int(255),
CONSTRAINT pk_purchases PRIMARY KEY(id),
FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;

CREATE TABLE injectables(
id                      int(255) auto_increment not null,
animal_id               int(255),
injectable_type         varchar(100),
application_date        datetime DEFAULT NULL,
injectable_name         varchar(100),
injectable_brand        varchar(100),
withdrawal_time         int(100),
effective_time          int(100),
description             text,
creation_time           varchar(100),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_injectables PRIMARY KEY(id),
CONSTRAINT fk_injectable_animal FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;


CREATE TABLE parents(
id                      int(255) auto_increment not null,
mother_id               int(255),
father_id               int(255),
animal_id               int(255),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_parents PRIMARY KEY(id),
CONSTRAINT fk_parents_animal FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;

CREATE TABLE incidents(
id                      int(255) auto_increment not null,
animal_id               int(255),
incident_date           datetime DEFAULT NULL,
incident_type           varchar(255),
description             text,
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_incidents PRIMARY KEY(id),
CONSTRAINT fk_incident_animal FOREIGN KEY(animal_id) REFERENCES animals(id)
)ENGINE=InnoDb;

CREATE TABLE notifications(
id                      int(255) auto_increment not null,
user_id                 int(255),
notification_date       datetime DEFAULT NULL,
notification_type       varchar(255),
notification_state      varchar(255),
description             text,
code                    int(100),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_notifications PRIMARY KEY(id),
CONSTRAINT fk_user_notification FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

-- ALTER TABLE `users` ADD validation_token VARCHAR(250) AFTER remember_token;