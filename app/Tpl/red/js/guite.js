// JavaScript Document
$(document).ready(function(e) {

});

function register(){
	$.layer({
		type: 2,
		shadeClose: true,
		title: false,
		closeBtn: [0, false],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: 'index.php?ctl=user&act=register'}
	});
}

function login(){
	$.layer({
		type: 2,
		shadeClose: true,
		title: false,
		closeBtn: [0, false],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: 'index.php?ctl=user&act=login'}
	});
}

function attestation(){
	$.layer({
		type: 2,
		shadeClose: true,
		title: false,
		closeBtn: [0, false],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: 'index.php?ctl=deal&act=bid&id='}
	});
}

function recharge(){
	$.layer({
		type: 2,
		shadeClose: true,
		title: false,
		closeBtn: [0, false],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: 'index.php?ctl=uc_money&act=incharge'}
	});
}

function buy(){
	$.layer({
		type: 2,
		shadeClose: true,
		title: false,
		closeBtn: [0, false],
		shade: [0.8, '#000'],
		border: [0],
		offset: ['20px',''],
		area: ['1000px', ($(window).height() - 50) +'px'],
		iframe: {src: 'index.php?ctl=deals'}
	});
}