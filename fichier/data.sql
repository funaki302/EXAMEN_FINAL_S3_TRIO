create database template_MVC;
use template_MVC;

create table user(
    id_user int auto_increment primary key,
    name varchar(200),
    email varchar(200),
    role varchar(100),
    status varchar(100),
    department varchar(200),
    phone varchar(15),
    join_date date,
    last_active varchar(200),
    pwd varchar(200)
);

insert into user(name,email,role,status,department,phone,join_date,last_active,pwd)
values ('Coralie Sanorana', 'coralie@example.com','admin','active','IT','0383593859','2026-01-27','2 hours ago','1234'),
('Ericka Sanorana', 'ericka@example.com','user','active','College','0384792434','2026-01-27','1 hours ago','1509'),
('Gastelle Sanorana', 'gastelle@example.com','user','active','Primary','0324143331','2026-01-27','3 hours ago','3001')
;

create table discussion(
    id_discussion int auto_increment primary key,
    title varchar(200),
    id_user1 int,
    id_user2 int,
    created_at date,
    updated_at date,
    foreign key (id_user1) references user(id_user),
    foreign key (id_user2) references user(id_user)
);

insert into 
discussion(title,id_user1, id_user2,created_at,updated_at) values
('Discu',1,2,'2026-01-28','2026-01-28'),
('Discu',2,3,'2026-01-28','2026-01-28');

create table messages(
    id_message int auto_increment primary key,
    id_discussion int,
    id_sender int,
    content text,
    sent_at date,
    foreign key (id_discussion) references discussion(id_discussion),
    foreign key (id_sender) references user(id_user)
);

/* create table products();

create table messages();

create table elements();
 */
