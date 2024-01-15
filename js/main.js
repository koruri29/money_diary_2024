window.onload = ()=> {
  // URLの取得
  const amount_input = document.getElementById('amount');
  if (amount_input !== undefined) {
    
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
		const outgo_option = document.getElementById('outgo');
		const income_option = document.getElementById('income');
		const outgo_radio = document.getElementsByClassName('i_minus')[0];
		const income_radio = document.getElementsByClassName('i_plus')[0];
		outgo.addEventListener('click', () => {
			console.log('true');
			outgo_radio.checked = true;
		});
		income.addEventListener('click', () => {
			income_radio.checked = true;
		});
	} 
}
	
	
	
	