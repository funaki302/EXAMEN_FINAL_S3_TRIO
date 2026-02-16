(SELECT 
d.id_discussion,
u.id_user,
u.name,
u.email,
u.phone,
u.role
FROM discussion d
JOIN user u on u.id_user = d.id_user2
WHERE d.id_user1 = 1
)
UNION 
(SELECT 
d.id_discussion,
u.id_user,
u.name,
u.email,
u.phone,
u.role
FROM discussion d
JOIN user u on u.id_user = d.id_user1
WHERE d.id_user2 = 1
);

insert into 
messages(id_discussion,id_sender,content,sent_at)
values (1,2,'Bonjour, comment ça va ?',NOW());