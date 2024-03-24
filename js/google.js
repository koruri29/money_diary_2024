// function onSignIn(googleUser) {
// 	console.log('hmm...')
//   let profile = googleUser.getBasicProfile();
//   const user_name = profile.getName();
//   const id_token = profile.id_token();

// 	let xhr = new XMLHttpRequest();
// 	xhr.open('POST', "../src/index.php");
// 	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
// 	xhr.onload = function() {
// 		console.log('Signed in as: ' + xhr.responseText);
// 		console.log('method ran');
// 		window.location.href = 'index.php';
// 	};
// 	xhr.send('id_token=' + id_token + '&user_name=' + user_name);
// }

// function onSignIn(googleUser) {
//   var profile = googleUser.getBasicProfile();
//   console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
//   console.log('Name: ' + profile.getName());
//   console.log('Image URL: ' + profile.getImageUrl());
//   console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
// }
import * as jose from '../node_modules/jose/dist/browser/util/decode_jwt.js'

window.handleCredentialResponse = (response) => {
	console.log('ok so far');
	console.log(response.credential);
	console.log(jose.decodeJwt(response.credential));

	// const responsePayload = jose.decodeJwt(response.credential);
	const responsePayload = response.credential;
	const user_name = responsePayload.name;
	const id_token = responsePayload.sub;

	let xhr = new XMLHttpRequest();
	xhr.open('POST', "../src/index.php");
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onload = function() {
		console.log('Signed in as: ' + xhr.responseText);
		console.log('method ran');
		// window.location.href = '../src/index.php';
	};
	xhr.send('id_token=' + id_token + '&user_name=' + user_name);
}
