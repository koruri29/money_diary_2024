CREATE DATABASE IF NOT EXISTS money_diary_202401;

use money_diary_202401;
GRANT ALL PRIVILEGES ON money_diary_202401.* TO money_diary_user@'localhost' IDENTIFIED BY '6PzNEpALtDB6';

-- use money_diary_202401;

CREATE TABLE tmp_users (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	email varchar(100) NOT NULL,
	token varchar(255) NOT NULL,
	expires datetime NOT NULL,
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE users (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	user_name varchar(50) NOT NULL,
	role int(2) DEFAULT 0 NOT NULL,
	email varchar(100) NOT NULL UNIQUE KEY,
	password varchar(255) NOT NULL,
	delete_flg int(1) NOT NULL DEFAULT 0,
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP
);



CREATE TABLE auto_login (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	user_id int(11) NOT NULL,
	token varchar(255) NOT NULL,
	expires datetime NOT NULL,
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY auto_login_user(user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE icons (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	icon_name varchar(50),
	html_tag varchar(100) NOT NULL,
	css_content varchar(10) NOT NULL,
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP
);

-- 費目カテゴリ
CREATE TABLE categories (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	user_id int(11) NOT NULL,
	category_name varchar(30) NOT NULL,
	item_order int(3) NOT NULL DEFAULT 0,
	icon_id int(11) NOT NULL DEFAULT 2,-- minusアイコン
	icon_color varchar(10) NOT NULL DEFAULT 'gray',
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY user_on_category (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY categorys_icon (icon_id) REFERENCES icons(id)
);

-- 財産カテゴリ
CREATE TABLE wallets (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	user_id int(11) NOT NULL,
	wallet_name varchar(30) NOT NULL,
	item_order int(3) NOT NULL DEFAULT 0,
	remain_control int(1) NOT NULL DEFAULT 0,-- 残高管理をするか否か
	remains int(11) NOT NULL DEFAULT 0,-- 残高
	icon_id int(11) NOT NULL DEFAULT 70,-- yenアイコン
	icon_color varchar(10) NOT NULL DEFAULT 'gray',
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY user_on_wallets (user_id) REFERENCES users (id) ON DELETE CASCADE,
	FOREIGN KEY wallets_icon (icon_id) REFERENCES icons (id)
);

-- 入出金アイテム
CREATE TABLE money_events (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	user_id int(11) NOT NULL,
	category_id int(11) NOT NULL,
	wallet_id int(11) NOT NULL DEFAULT 1,
	`option` int(1) NOT NULL,-- 支出or収入
	amount int(11) NOT NULL,
	date datetime NOT NULL,-- 入出金が起きた日時
	other varchar(255), -- 備考
	created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY user_on_entry(user_id) REFERENCES users(id),
	FOREIGN KEY category_on_entry(category_id) REFERENCES categories(id),
	FOREIGN KEY wallet_on_entry(wallet_id) REFERENCES wallets(id)
);


INSERT INTO icons (icon_name, html_tag, css_content) VALUES
	('i_plus', '<i class="fa-solid fa-plus"></i>', '2b'),
	('i_minus', '<i class="fa-solid fa-minus"></i>', 'f068'),
	('i_food', '<i class="fa-solid fa-utensils"></i>', 'f2e7'),
	('i_tv', '<i class="fa-solid fa-tv"></i>', 'f26c'),
	('i_phone', '<i class="fa-solid fa-phone"></i>', 'f095'),
	('i_mobile-phone', '<i class="fa-solid fa-mobile-screen-button"></i>', 'f3cd'),
	('i_envelope', '<i class="fa-regular fa-envelope"></i>', 'f0e0'),
	('i_bluetooth', '<i class="fa-brands fa-bluetooth-b"></i>', 'f294'),
	('i_train', '<i class="fa-solid fa-train-subway"></i>', 'f239'),
	('i_car', '<i class="fa-solid fa-car"></i>', 'f1b9'),
	('i_bus', '<i class="fa-solid fa-bus"></i>', 'f207'),
	('i_bicycle', '<i class="fa-solid fa-bicycle"></i>', 'f206'),
	('i_plane', '<i class="fa-solid fa-plane"></i>', 'f072'),
	('i_comb', '<i class="fa-solid fa-ruler"></i>', 'f545'),
	('i_book', '<i class="fa-solid fa-book"></i>', 'f02d'),
	('i_school', '<i class="fa-solid fa-school"></i>', 'f549'),
	('i_music', '<i class="fa-solid fa-music"></i>', 'f001'),
	('i_biz-bag', '<i class="fa-solid fa-briefcase"></i>', 'f0b1'),
	('i_light-bulb', '<i class="fa-solid fa-lightbulb"></i>', 'f0eb'),
	('i_graduation-cap', '<i class="fa-solid fa-graduation-cap"></i>', 'f19d'),
	('i_clothe', '<i class="fa-solid fa-shirt"></i>', 'f553'),
	('i_socks', '<i class="fa-solid fa-socks"></i>', 'f696'),
	('i_glasses', '<i class="fa-solid fa-glasses"></i>', 'f530'),
	('i_jewelry', '<i class="fa-regular fa-gem"></i>', 'f3a5'),
	('i_animal-paw', '<i class="fa-solid fa-paw"></i>', 'f1b0'),
	('i_hospital', '<i class="fa-solid fa-hospital"></i>', 'f0f8'),
	('i_house', '<i class="fa-solid fa-house"></i>', 'f015'),
	('i_cup', '<i class="fa-solid fa-mug-saucer"></i>', 'f0f4'),
	('i_wine', '<i class="fa-solid fa-wine-glass"></i>', 'f4e3'),
	('i_box', '<i class="fa-solid fa-box"></i>', 'f466'),
	('i_gift-box', '<i class="fa-solid fa-gift"></i>', 'f06b'),
	('i_pen', '<i class="fa-solid fa-pen"></i>', 'f304'),
	('i_smoking', '<i class="fa-solid fa-smoking"></i>', 'f48d'),
	('i_soccer', '<i class="fa-solid fa-futbol"></i>', 'f1e3'),
	('i_baseball', '<i class="fa-solid fa-baseball-bat-ball"></i>', 'f432'),
	('i_basketball', '<i class="fa-solid fa-basketball"></i>', 'f434'),
	('i_walking', '<i class="fa-solid fa-person-walking"></i>', 'f554'),
	('i_swimming', '<i class="fa-solid fa-person-swimming"></i>', 'f5c4'),
	('i_calculator', '<i class="fa-solid fa-calculator"></i>', 'f1ec'),
	('i_microphone', '<i class="fa-solid fa-microphone"></i>', 'f130'),
	('i_headphone', '<i class="fa-solid fa-headphones"></i>', 'f025'),
	('i_rotate', '<i class="fa-solid fa-arrows-rotate"></i>', 'f021'),
	('i_recycle', '<i class="fa-solid fa-recycle"></i>', 'f1b8'),
	('i_alphabet', '<i class="fa-solid fa-font"></i>', 'f031'),
	('i_smile', '<i class="fa-regular fa-face-smile"></i>', 'f118'),
	('i_face-frown', '<i class="fa-regular fa-face-frown"></i>', 'f119'),
	('i_drop', '<i class="fa-solid fa-droplet"></i>', 'f043'),
	('i_comment', '<i class="fa-regular fa-message"></i>', 'f27a'),
	('i_blocks', '<i class="fa-solid fa-shapes"></i>', 'f61f'),
	('i_play', '<i class="fa-solid fa-play"></i>', 'f04b'),
	('i_earth', '<i class="fa-solid fa-earth-asia"></i>', 'f57e'),
	('i_location-pin', '<i class="fa-solid fa-location-dot"></i>', 'f3c5'),
	('i_tree', '<i class="fa-solid fa-tree"></i>', 'f1bb'),
	('i_bookmark', '<i class="fa-regular fa-bookmark"></i>', 'f02e'),
	('i_wifi', '<i class="fa-solid fa-wifi"></i>', 'f1eb'),
	('i_flag', '<i class="fa-solid fa-font-awesome"></i>', 'f2b4'),
	('i_cloud', '<i class="fa-solid fa-cloud"></i>', 'f0c2'),
	('i_sun', '<i class="fa-regular fa-sun"></i>', 'f185'),
	('i_mountain', '<i class="fa-solid fa-mountain-sun"></i>', 'e52f'),
	('i_mail-box', '<i class="fa-solid fa-inbox"></i>', 'f01c'),
	('i_palette', '<i class="fa-solid fa-palette"></i>', 'f53f'),
	('i_paperclip', '<i class="fa-solid fa-paperclip"></i>', 'f0c6'),
	('i_gear', '<i class="fa-solid fa-gear"></i>', 'f013'),
	('i_check-mark', '<i class="fa-solid fa-check"></i>', 'f00c'),
	('i_star', '<i class="fa-regular fa-star"></i>', 'f005'),
	('i_heart', '<i class="fa-regular fa-heart"></i>', 'f004'),
	('i_person', '<i class="fa-solid fa-person"></i>', 'f183'),
	('i_piggy-bank', '<i class="fa-solid fa-piggy-bank"></i>', 'f4d3'),
	('i_wallet', '<i class="fa-solid fa-wallet"></i>', 'f555'),
	('i_yen', '<i class="fa-solid fa-yen-sign"></i>', 'f157'),
	('i_dollar', '<i class="fa-solid fa-dollar-sign"></i>', '24'),
	('i_euro', '<i class="fa-solid fa-euro-sign"></i>', 'f153'),
	('i_dollar-bag', '<i class="fa-solid fa-sack-dollar"></i>', 'f81d'),
	('i_credit-card', '<i class="fa-solid fa-money-check-dollar"></i>', 'f53d'),
	('i_triangle-exclamation', '<i class="fa-solid fa-triangle-exclamation"></i>', 'f071'),
	('i_circle-question', '<i class="fa-regular fa-circle-question"></i>', 'f059'),
	('i_three-dots', '<i class="fa-solid fa-ellipsis"></i>', 'f141'),
	('i_scissors', '<i class="fa-solid fa-scissors"></i>', 'f0c4');
