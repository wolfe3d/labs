//use https://obfuscator.io/
var numBunnies = <?=$numBunnies?>;
var numCats = <?=$numCats?>;
var numDogs = <?=$numDogs?>;
var canvas = document.getElementById('myCanvas');
var ctx = canvas.getContext('2d');

var painting = document.getElementById('paint');
var paint_style = getComputedStyle(painting);
canvas.width = parseInt(paint_style.getPropertyValue('width'));
canvas.height = parseInt(paint_style.getPropertyValue('height'));
var spanX = document.getElementById("mX"); 
var spanY = document.getElementById("mY"); 
var mouse = {x: 0, y: 0};

ctx.lineWidth = 1;
ctx.rect(0, 0, canvas.width, canvas.height);
ctx.stroke();
ctx.closePath();

var i;
for (i = 0; i < numBunnies; i++) 
{ 
	var randomX = Math.random()*canvas.width;
	var randomY = Math.random()*canvas.height;
	ctx.beginPath();
	ctx.arc(randomX, randomY, 2, 0, 2 * Math.PI); /*(centerX, centerY, radius, start, end)*/
	ctx.stroke(); 
	ctx.closePath();
}

ctx.strokeStyle = '#0066ff';
for (i = 0; i < numCats; i++) 
{ 
	var randomX = Math.random()*canvas.width;
	var randomY = Math.random()*canvas.height;
	ctx.beginPath();
	ctx.arc(randomX, randomY, 2.5, 0, 2 * Math.PI); /*(centerX, centerY, radius, start, end)*/
	ctx.stroke(); 
	ctx.closePath();
}
ctx.beginPath(); 

ctx.strokeStyle = '#ff0066';
for (i = 0; i < numDogs; i++) 
{ 
	var randomX = Math.random()*canvas.width;
	var randomY = Math.random()*canvas.height;
	ctx.beginPath();
	ctx.arc(randomX, randomY, 3, 0, 2 * Math.PI); /*(centerX, centerY, radius, start, end)*/
	ctx.stroke(); 
	ctx.closePath();
}
ctx.beginPath(); 

canvas.addEventListener('mousemove', function(e) {
  mouse.x = e.pageX - this.offsetLeft;
  mouse.y = e.pageY - this.offsetTop;
  spanX.textContent = mouse.x;
  spanY.textContent = mouse.y;
}, false);

ctx.lineWidth = 3;
ctx.lineJoin = 'round';
ctx.lineCap = 'round';
ctx.strokeStyle = '#00CC99';

canvas.addEventListener('mousedown', function(e) {
	var quadratLength = parseInt(document.getElementById("quadrantSize").value);
	if(mouse.x + quadratLength <= canvas.width && mouse.y + quadratLength <= canvas.height)
	{	
		ctx.rect(mouse.x,  mouse.y, quadratLength, quadratLength);
		ctx.stroke();
	}
}, false);