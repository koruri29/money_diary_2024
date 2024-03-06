window.onload = ()=> {
	//ハンバーガーメニュー(SP)
	if (window.innerWidth <= 768) {
		const nav = document.getElementsByClassName('nav-sp')[0];
		$('.hamburger').on('click', () => {
			$('.nav').toggleClass('nav-sp-show');
			$('.hamburger').toggleClass('active');
			$('.sp-nav').fadeToggle('0.5s');
		});
		$('.global-nav').on('click', () => {
			$('.nav').toggleClass('nav-sp-show');
			$('.hamburger').toggleClass('active');
			$('.sp-nav').fadeToggle('fast');
		});
	}


  const amount_input = document.getElementById('amount');
  if (amount_input !== null) {

		//「金額」欄にデフォルトでフォーカスあてる
		amount_input.focus();


		//「昨日」・「今日」ボタンの挙動
		// クリックで本日または本日の1日前の日付に変更
		const date_input = document.getElementById('date');
		const yesterday = document.getElementById('yesterday');
		yesterday.addEventListener('click', (e) => {
			e.preventDefault();
			const date_root = new Date();
			let year = date_root.getFullYear();
			let month = date_root.getMonth();
			let date = date_root.getDate() - 1;

			const yesterday = new Date(year, month, date);
			year = yesterday.getFullYear();
			month = yesterday.getMonth() + 1;
			date = yesterday.getDate();

			const full_date = String(date).padStart(2, '0');
			const full_month = String(month).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + full_date;
		});

		const today = document.getElementById('today');
		today.addEventListener('click', (e) => {
			e.preventDefault();
			const date_root = new Date();
			const year = date_root.getFullYear();
			const month = date_root.getMonth() + 1;
			const date = date_root.getDate();

			const full_month = String(month).padStart(2, '0');
			const full_date = String(date).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + full_date;
		});


		//option「収入」選んだら同じカテゴリーが選ばれるようにする
		//HTML構成変わるとgetElementsByClassNameも変更が必要
		const option_outgo = document.getElementById('outgo');
		const option_income = document.getElementById('income');
		const radio_outgo = document.getElementsByClassName('i_minus')[0];
		const radio_income = document.getElementsByClassName('i_plus')[0];
		const div_outgo = document.querySelector('.outgo');
		const div_income = document.querySelector('.income');


		option_outgo.addEventListener('change', () => {
			radio_outgo.checked = true;
			div_income.classList.add('hidden');
			div_outgo.classList.remove('hidden');
			amount_input.focus();
		});
		option_income.addEventListener('change', () => {
			radio_income.checked = true;
			div_outgo.classList.add('hidden');
			div_income.classList.remove('hidden');
			amount_input.focus();
		});
	}

	//削除ボタンがある画面（一般TOP、検索、adminユーザー一覧）
	const delete_btns = document.getElementsByClassName('delete');
	const forms = document.getElementsByName('delete_form');

	if (delete_btns.length > 0) {
		for (let i = 0; i < delete_btns.length; i++) {
			delete_btns[i].addEventListener('click', (e) => {
				e.preventDefault();
				if (confirm('アイテムを削除してよろしいですか？')) {
					forms[i].submit();
				}
			});
		}
	}

	const btn_this_month = document.getElementById('this_month');
	const btn_prev_month = document.getElementById('prev_month');
	if (btn_this_month !== null && prev_month !== null) {
		const min_date_input = document.getElementById('min_date');
		const max_date_input = document.getElementById('max_date');

		// 「今月」ボタンを押したときに、日付の範囲を今月に設定
		btn_this_month.addEventListener('click', e => {
			e.preventDefault();

			// 日付の調整
			const date_root = new Date();
			const year = date_root.getFullYear();
			const month = date_root.getMonth() + 1;
			let next_month;
			if (month === 12) {
				next_month = 1;
			} else {
				next_month = month + 1;
			}
			const full_month = String(month).padStart(2, '0');
			const date_root_last = new Date(year, next_month, 0);// 来月の0日目(今月の末日)
			const date_last = date_root_last.getDate();
			const full_last_date = String(date_last).padStart(2, '0');

			min_date_input.value = year + '-' + full_month + '-01';
			max_date_input.value = year + '-' + full_month + '-' + full_last_date;
		});

		// 「前月」ボタンを押したときに、日付の範囲を前月に設定
		btn_prev_month.addEventListener('click', e => {
			e.preventDefault();

			// 日付の調整
			const date_root = new Date();
			let year = date_root.getFullYear();
			const this_month = date_root.getMonth() + 1;
			let prev_month;
			if (this_month === 1) {
				year -= 1;
				prev_month = 12;
			} else {
				prev_month = this_month - 1;
			}
			const full_prev_month = String(prev_month).padStart(2, '0');
			const date_root_last = new Date(year, this_month - 1, 0);// 今月の0日目(前月の末日)
			const date_last = date_root_last.getDate();
			const full_last_date = String(date_last).padStart(2, '0');

			min_date_input.value = year + '-' + full_prev_month + '-01';
			max_date_input.value = year + '-' + full_prev_month + '-' + full_last_date;
		});
	}
}
