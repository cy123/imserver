<?php

require  './service/Sqlite.php';
require 'function.php';

$user_sql = "
   DROP TABLE IF EXISTS `users`;
   CREATE TABLE `users`  (
  `qq` int(9) NOT NULL PRIMARY KEY,
  `nickname` varchar(50)  NOT NULL,
  `pwd` char(16)  NOT NULL,
  `fd` int(255) DEFAULT ''
);
";
$friend_sql = "
DROP TABLE IF EXISTS `friends`;
   CREATE TABLE `friends`  (
  `qq` int(9) NOT NULL ,
  `friend_qq` int(9)  NOT NULL
  )
";

// 聊天消息记录表
$message_sql = "
DROP TABLE IF EXISTS `message`;
   CREATE TABLE `message`  (
  `qq` int(9) NOT NULL ,
  `to_qq` int(9)  NOT NULL,
  `message` varchar(500) not null,
  `send_success` tinyint not null,
  `is_read` tinyint not null
  )
";
$upsql = "
alter table users add session_id varchar(36)  

";
$f_sql = "
     select f.friend_qq from users as u 
            inner join friends f on u.qq=f.qq
            where u.qq=46
";
$add_friend_sql = "
INSERT INTO friends(qq,friend_qq)values(46,45);
";
//$sql = "
//     INSERT INTO users (id,username,pwd,fd)
//      VALUES (1,'chen','123456',1);
//      INSERT INTO users (id,username,pwd,fd)
//      VALUES (2,'li','123456',2);
//";
$sql = "
    select * from users;
";
$upuser_sql = "
UPDATE users set session_id=99 where qq=46
";
$find_sql = "
 select * from users as u 
            left join friends f on u.qq=f.qq
            where u.session_id='5ac4f8fb-84c3-471e-a094-cc897e7a2556'

";
$u = "
select * from users 
";
$update_user = "
alter table users add avatar varchar(200)
";
$message = "
    select * from message
";
$del = "
    delete from users;
";
$sql = "
UPDATE users SET  `session_id` = '63b9562f-ad57-4989-92ad-adb725e7a92c' , `fd` = 1 WHERE `qq` = 6001
";
\App\service\Sqlite::exec($sql);
return;
$ret =\App\service\Sqlite::query($u);
//var_dump($db->querySingle($sql, true));
while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
    var_dump($row);
}