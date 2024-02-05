// window.onload = () => {
	//共通
	const EMAIL_PATTERN = /^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/;
	const div_errors = document.getElementById('disp_errors');
	// const form = document.getElementsByName('form')[0];
	const send_btn = document.getElementsByClassName('send_btn')[0];

	function showErrors(errors) {
		for (let i = 0; i < errors.length; i++) {
			p = document.createElement('p');
			p.classList.add('error');
			p.textContent = errors[i];
			div_errors.appendChild(p);
		}
	}

	function deleteErrors(elem_parent) {
		while (elem_parent.firstChild) {
			elem_parent.removeChild(elem_parent.firstChild);
		}
	}

	//仮登録画面
	if (document.getElementById('tmp_register_btn') !== null) {
		const email_input = document.getElementById('email');

		send_btn.addEventListener('click', (e) => {
			e.preventDefault();
			send_btn.disabled = true;

			deleteErrors(div_errors);

			let errors = [];
			if (email_input.value === '') {
				errors.push ('メールアドレスを入力してください。');
			} else if (! EMAIL_PATTERN.test(email_input.value)) {
				errors.push('有効なメールアドレスを入力してください。');
			}
			if (email_input.value.length > 100) {
				errors.push('メールアドレスは100文字以内で入力してください。');
			}

			if (errors.length > 0) {
				showErrors(errors);
				send_btn.disabled = false;
			} else {
				document.form.submit();
			}
		});

		//本登録画面
	} else if (document.getElementById('main_register_btn') !== null) {
		const user_name_input = document.getElementById('user_name');

		send_btn.addEventListener('click', (e) => {
			e.preventDefault();
			send_btn.disabled = true;

			deleteErrors(div_errors);

			let errors = [];
			if (user_name_input.value === '') {
				errors.push('ユーザー名を入力してください。')
			}
			if (user_name_input.value.length > 50) {
				errors.push('ユーザー名は50文字以内で入力してください。');
			}

			if (errors.length > 0) {
				showErrors(errors);
				send_btn.disabled = false;
			} else {
				document.form.submit();
			}
		});

		//入出金登録画面(ログイン後トップ画面)
	} else if (document.getElementById('event-register-btn') !== null) {
		const date_input = document.getElementById('date');
		const options = document.getElementsByName('option');
		const amount_input = document.getElementById('amount');
		const categories = document.getElementsByName('category_id');

		send_btn.addEventListener('click', (e) => {
			e.preventDefault();
			send_btn.disabled = true;

			deleteErrors(div_errors);

			let errors = [];
			//日付
			const date = new Date(date_input.value)
			const date_start = new Date(1950, 1, 1, 0, 0, 0);
			const date_end = new Date(2050, 12, 31, 0, 0, 0);
			if (date_input.value === '' || isNaN(date.getDate()) ||date < date_start || date > date_end) {// 2つ目の判定は日付が有効か否か
				errors.push('有効な日付を指定してください。');
			}
			//収入or支出
			if (! options[0].checked && ! options[1].checked) {
				errors.push('収入か支出を選択してください。');
			}
			//金額
			if (! isNaN(amount_input.value) && amount_input.value <= 0) {
				errors.push('金額は正の整数で入力してください')
			}
			//カテゴリー選択
			let is_category_checked = false;
			for(let category of categories) {
				if (category.checked) {
					is_category_checked = true;
					break;
				}
			}
			if (! is_category_checked) {
				errors.push('カテゴリーを選択してください。');
			}

			if (errors.length > 0) {
				showErrors(errors);
				send_btn.disabled = false;
			} else {
				document.form.submit();
			}
		});
	}



// }
