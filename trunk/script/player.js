n=0;
nw=100;
imgpics=imgpics.split('|');
imglinks=imglinks.split('|');
imgtexts=imgtexts.split('|');

document.write(
'<style type="text/css">' +
	//'#player{padding:3px;}' +
	//'#player img{padding:1px;}' +
	'#player span{background-color:#A6DA6B;line-height:15px;padding:0 3px 0 3px;border:1px solid #fff;margin:1px;}' +
	//'#player .gray{background-color:#A6DA6B;line-height:15px;padding:0 3px 0 3px;border:1px solid #fff;margin:2px;}' +
	'#player .gray a{color:white;text-decoration:none;}' +
	'#player .gray a:hover{color:white;text-decoration:none;}' +
	//'#player .dblack{background-color:#A6DA6B;line-height:15px;padding:0 3px 0 3px;border:1px solid #fff;margin:2px;}' +
	'#player .dblack a{color:black;font-weight:bold;text-decoration:none;}' +
	'#player .dblack a:hover{color:white;text-decoration:none;}' +
'</style>'
);
document.write(
'<div id="player" class=\'img\'>' +
'<div id="player_img">' +
	'<a id="imglink" href="" target="_blank"><img id="imgpic" src="" border=0 style="display:none;"></a>' +
'</div>' +
'<div id="imgtext" style="display:none;text-align:center;"></div>' +
'<div id="player_nav" style="position:absolute;width:'+nw+'px;z-index:99;display:none">'
);
document.write(
			'<span id="nav_0" class="gray">' +
				'<a href="javascript:showimg(\'0\');" target=_self>1</a>' +
			'</span>'
);
for(i=1;i<imgnum;i++){
	t=i+1;
	document.write(
			'<span id="nav_' + i + '" class="dblack" onmouseover="this.className=\'gray\';"  onmouseout="this.className=\'dblack\';">' +
			'<a href="javascript:showimg(\'' + i + '\');" target=_self>' + t + '</a>' +
			'</span>'
	);
}
document.write(
'</div></div>'
);

var  daps    =  document.getElementById("player_nav").style;  
var  tt      =  document.getElementById("imgpic");  
var  ttop    =  tt.offsetTop+165;          //TT控件的定位点高  
var  tleft   =  tt.offsetLeft+135;        //TT控件的定位点宽  

while(tt = tt.offsetParent){
	ttop  += tt.offsetTop;
	tleft += tt.offsetLeft;
}  

//alert(tleft);

daps.top	=  ttop;  //层的  Y  坐标  
daps.left	=  tleft;    //层的  X  坐标  
daps.display  =  "";

var do_transition;
var tcount = 14;

var garTransitions = new Array();
garTransitions[0] = "progid:DXImageTransform.Microsoft.RandomDissolve()";
garTransitions[1] = "progid:DXImageTransform.Microsoft.Iris(irisStyle='star', motion='out')";
garTransitions[2] = "progid:DXImageTransform.Microsoft.Stretch(stretchStyle='push')";
garTransitions[3] = "progid:DXImageTransform.Microsoft.Stretch(stretchStyle='pop')";
garTransitions[4] = "progid:DXImageTransform.Microsoft.Fade(duration=2,overlap=0)";
garTransitions[5] = "progid:DXImageTransform.Microsoft.GradientWipe(duration=2,gradientSize=0.25,motion=forward )";
garTransitions[6] = "progid:DXImageTransform.Microsoft.Wheel(duration=2,spokes=16)";
garTransitions[7] = "progid:DXImageTransform.Microsoft.RadialWipe(duration=2,wipeStyle=CLOCK)";
garTransitions[8] = "progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)";
garTransitions[9] = "progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)";
garTransitions[10]= "progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)";
garTransitions[11]= "progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)";
garTransitions[12]= "progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)";
garTransitions[13]= "progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)";

function showimg(n){
	if(imgpics[n]){
		if (document.all){
			do_transition = Math.floor(Math.random() * tcount);
			document.all.player.style.filter=garTransitions[do_transition];
			document.all.player.filters[0].Apply();			
		}

		document.getElementById("imgpic").style.display='';
		document.getElementById("imgpic").src=imgpics[n];
		document.getElementById("imglink").href=imglinks[n];
		if(imgtexts[n]){
			document.getElementById("imgtext").innerHTML='<h3><a href="' + imglinks[n] + '" target="_blank">' + imgtexts[n] + '</a></h3>';
			document.getElementById("imgtext").style.display = "";
		}else{
			document.getElementById("imgtext").style.display = "none";
		}
		for (i=0; i<imgnum; i++)
		{
			if(i==n){
				document.getElementById("nav_"+i).className='gray';
			}else{
				document.getElementById("nav_"+i).className='dblack';
			}
		}

		if (document.all) {			
			document.all.player.filters[0].Play();		
		}
	}
}
function changeimg(n){
	if (n>=imgnum){
		n=0;
	}
	showimg(n);
	n++;
	setTimeout('changeimg('+n+')',3000);
}

setTimeout('changeimg('+n+')',0);