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
		
			full_month = String(month).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + day;
		});
		
		const today = document.getElementById('today');
		today.addEventListener('click', (e) => {
			e.preventDefault();
			const date = new Date();
			const year = date.getFullYear();
			const month = date.getMonth() + 1;
			const day = date.getDate();
		
			full_month = String(month).padStart(2, '0');
			date_input.value = year + '-' + full_month + '-' + day;
		});
		
		
		//option「収入」選んだら同じカテゴリーが選ばれるようにする
		//HTML構成変わるとgetElementsByClassNameも変更が必要
		const outgo = document.getElementById('outgo');
		const income = document.getElementById('income');
		const category_outgo = document.getElementsByClassName('i_minus')[0];
		const category_income = document.getElementsByClassName('i_plus')[0];

		outgo.addEventListener('change', () => {
			category_outgo.checked = true;
			amount_input.focus();
		});
		income.addEventListener('change', () => {
			category_income.checked = true;
			amount_input.focus();
	});
	} 
}
	
	
	
	