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


		//「昨日」・「今日」ボタンクリックで本日の1日前の日付に変更
		const date_input = document.getElementById('date');
		const yesterday = document.getElementById('yesterday');
		yesterday.addEventListener('click', (e) => {
			e.preventDefault();
			const date = new Date();
			const year = date.getFullYear();
			const month = date.getMonth() + 1;
			const day = date.getDate() - 1;

			full_day = String(day).padStart(2, '0');
			full_month = String(month).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + full_day;
		});

		const today = document.getElementById('today');
		today.addEventListener('click', (e) => {
			e.preventDefault();
			const date = new Date();
			const year = date.getFullYear();
			const month = date.getMonth() + 1;
			const day = date.getDate();

			full_month = String(month).padStart(2, '0');
			full_day = String(day).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + full_day;
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
	if (delete_btns.length > 0) {
		const forms = {};
		for (let delete_btn of delete_btns) {
			let i = 0;
			let form = document.getElementsByName('delete_form' + i);
			delete_btn.addEventListener('click', (e) => {
				e.preventDefault();
				if (confirm('アイテムを削除してよろしいですか？')) {
					form[0].submit();//2月28日ここまで
				}
			});
			i++;
		}
	}
}
