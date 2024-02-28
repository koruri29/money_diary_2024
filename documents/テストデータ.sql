use money_diary_202401;

INSERT INTO users (user_name, email, password) VALUES ('test太郎', 'test888@example.com', '$2y$10$BQUIdha1rbxjMopkafToFuJbCCeh3AZT8jMVp0SUI8zibwNUt6yHS');
INSERT INTO users (user_name, email, password) VALUES ('test花子', 'test2@example.com', '$2y$10$BQUIdha1rbxjMopkafToFuJbCCeh3AZT8jMVp0SUI8zibwNUt6yHS');
INSERT INTO users (user_name, email, password) VALUES ('test次郎', 'test3@example.com', '$2y$10$BQUIdha1rbxjMopkafToFuJbCCeh3AZT8jMVp0SUI8zibwNUt6yHS');
INSERT INTO users (user_name, email, role, password) VALUES ('test次郎', 'test3@example.com', 99. '$2y$10$BQUIdha1rbxjMopkafToFuJbCCeh3AZT8jMVp0SUI8zibwNUt6yHS');

INSERT INTO wallets (user_id, wallet_name) VALUES (1, '現金');

-- 以下はブラウザでユーザー登録をしてから
INSERT INTO money_events (
	user_id,
	category_id,
	wallet_id,
	option,
	amount,
	date,
	other
) VALUES (
	1,
	1,
	1,
	0,
	3000,
	'2024-01-01 00:00:00',
	'テストデータです。'
);



