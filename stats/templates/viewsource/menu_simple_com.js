/*(c) Ger Versluis 2000 version simple 24 September 2002 
You may use this script on non commercial sites. 
For info write to menus@burmees.nl*/

var AgntUsr=navigator.userAgent.toLowerCase(),
AppVer=navigator.appVersion.toLowerCase(),
DomYes=document.getElementById?1:0,
NavYes=AgntUsr.indexOf("mozilla")!=-1&&AgntUsr.indexOf("compatible")==-1?1:0,
ExpYes=AgntUsr.indexOf("msie")!=-1?1:0,
Opr=AgntUsr.indexOf("opera")!=-1?1:0,
DomNav=DomYes&&NavYes?1:0,
DomExp=DomYes&&ExpYes?1:0,
Nav4=NavYes&&!DomYes&&document.layers?1:0,
Exp4=ExpYes&&!DomYes&&document.all?1:0,
MacCom=(AppVer.indexOf("mac")!= -1)?1:0,
MacExp4=(MacCom&&AppVer.indexOf("msie 4")!= -1)?1:0,
MacExp5=(MacCom&&AppVer.indexOf("msie 5")!= -1)?1:0,
Exp6Plus=(AppVer.indexOf("msie 6")!= -1||AppVer.indexOf("msie 7")!= -1)?1:0,
Mac4=(MacCom&&(Nav4||Exp4))?1:0,
PosStrt=(NavYes||ExpYes)&&!Opr?1:0,
FLoc,ScLoc,DcLoc,
SWinW,SWinH,FWinW,FWinH,SLdAgnWin,FColW,SColW,DColW,
FrstCreat=1,RLvl=0,Ldd=0,Crtd=0,IniFlg,AcrssFrms=1,
FrstCntnr=null,CurOvr=null,CloseTmr=null,CntrTxt,TxtClose,ImgStr,ShwFlg=0,
M_StrtTp=StartTop,M_StrtLft=StartLeft,StaticPos=0,
LftXtra=DomNav?LeftPaddng:0,TpXtra=DomNav?TopPaddng:0,FStr="",
M_Hide=Nav4?"hide":"hidden",M_Show=Nav4?"show":"visible",
Par=MenuUsesFrames?parent:window,
Doc=Par.document,
Bod=Doc.body,
Trigger=NavYes?Par:Bod;
var InitLdd=0,P_X=DomYes?"px":"";

	if(MacExp4||MacExp5)LdTmr=setInterval("ChckInitLd()",100);
	else{	if(Trigger.onload)Dummy=Trigger.onload;
		if(DomNav&&!Opr)Trigger.addEventListener("load",Go,false);
		else Trigger.onload=Go}

function ChckInitLd(){
	InitLdd=(MenuUsesFrames)?(Par.document.readyState=="complete"&&Par.frames[FirstLineFrame].document.readyState=="complete"&&Par.frames[SecLineFrame].document.readyState=="complete")?1:0:(Par.document.readyState=="complete")?1:0;
	if(InitLdd){clearInterval(LdTmr);Go()}}

function Dummy(){return}

function CnclSlct(){return false}

function RePos(){
	FWinW=ExpYes?FLoc.document.body.clientWidth:FLoc.innerWidth;
	FWinH=ExpYes?FLoc.document.body.clientHeight:FLoc.innerHeight;
	SWinW=ExpYes?ScLoc.document.body.clientWidth:ScLoc.innerWidth;
	SWinH=ExpYes?ScLoc.document.body.clientHeight:ScLoc.innerHeight;
	if(MenuCentered.indexOf("justify")!=-1&&FirstLineHorizontal){
		ClcJus();
		var P=FrstCntnr.FrstMbr,W=Menu1[5],a=BorderBtwnElmnts?NoOffFirstLineMenus+1:2,i;
		FrstCntnr.style.width=NoOffFirstLineMenus*W+a*BorderWidth+P_X;
		for(i=0;i<NoOffFirstLineMenus;i++){
			P.style.width=W-LftXtra+P_X;
			if(P.ai&&!RightToLeft)P.ai.style.left=BottomUp?W-Arrws[10]-2+P_X:W-Arrws[4]-2+P_X;
			P=P.PrvMbr}}
	StaticPos=-1;
	if(TargetLoc)ClcTrgt();ClcLft();ClcTp();
	PosMenu(FrstCntnr,StartTop,StartLeft)}

function UnLdd(){
	if(CloseTmr)clearTimeout(CloseTmr);
	Ldd=0;Crtd=0;
	if(!Nav4)LdTmr=setInterval("ChckLdd()",100)}

function ChckLdd(){
	if(!ExpYes){
		if(ScLoc.document.body){clearInterval(LdTmr);Go()}}
	else if(ScLoc.document.readyState=="complete"){if(LdTmr)clearInterval(LdTmr);Go()}}

function NavLdd(e){
	if(e.target!=self)routeEvent(e);
	if(e.target==ScLoc)Go()}

function ReDoWhole(){
	if(SWinW!=ScLoc.innerWidth||SWinH!=ScLoc.innerHeight||FWinW!=FLoc.innerWidth||FWinH!=FLoc.innerHeight)Doc.location.reload()}

function Go(){
	if(!Ldd&&PosStrt){
		BeforeStart();
		status="Building menu";
		Crtd=0;Ldd=1;
		FLoc=MenuUsesFrames?parent.frames[FirstLineFrame]:window;
		ScLoc=MenuUsesFrames?parent.frames[SecLineFrame]:window;
		DcLoc=MenuUsesFrames?parent.frames[DocTargetFrame]:window;
		if(MenuUsesFrames){
			if(!FLoc){FLoc=ScLoc;if(!FLoc){FLoc=ScLoc=DcLoc;if(!FLoc)FLoc=ScLoc=DcLoc=window}}
			if(!ScLoc){ScLoc=DcLoc;if(!ScLoc)ScLoc=DcLoc=FLoc}
			if(!DcLoc)DcLoc=ScLoc}
		if(FLoc==ScLoc)AcrssFrms=0;
		if(AcrssFrms)FirstLineHorizontal=MenuFramesVertical?0:1;
		FWinW=ExpYes?FLoc.document.body.clientWidth:FLoc.innerWidth;
		FWinH=ExpYes?FLoc.document.body.clientHeight:FLoc.innerHeight;
		SWinW=ExpYes?ScLoc.document.body.clientWidth:ScLoc.innerWidth;
		SWinH=ExpYes?ScLoc.document.body.clientHeight:ScLoc.innerHeight;
		FColW=Nav4?FLoc.document:FLoc.document.body;
		SColW=Nav4?ScLoc.document:ScLoc.document.body;
		DColW=Nav4?DcLoc.document:ScLoc.document.body;
		if(TakeOverBgColor){
			if(ExpYes)FColW.style.backgroundColor=AcrssFrms?SColW.bgColor:DColW.bgColor;
			else FColW.bgColor=AcrssFrms?SColW.bgColor:DColW.bgColor}
		if(MenuCentered.indexOf("justify")!=-1&&FirstLineHorizontal)ClcJus();
		if(FrstCreat||FLoc==ScLoc)FrstCntnr=CreateMenuStructure("Menu",NoOffFirstLineMenus,null);
		else CreateMenuStructureAgain("Menu",NoOffFirstLineMenus);
		if(TargetLoc)ClcTrgt();
		ClcLft();ClcTp();
		PosMenu(FrstCntnr,StartTop,StartLeft);
		IniFlg=1;Initiate();
		Crtd=1;
		SLdAgnWin=ExpYes?ScLoc.document.body:ScLoc;
		SLdAgnWin.onunload=UnLdd;
		Trigger.onresize=Nav4?ReDoWhole:RePos;
		AfterBuild();
		if(Nav4&&FrstCreat){Trigger.captureEvents(Event.LOAD);Trigger.onload=NavLdd}
		if(FrstCreat)Dummy();
		FrstCreat=0;
		if(MenuVerticalCentered=="static"&&!AcrssFrms)setInterval("KeepPos()",250)}
		status=""}

function KeepPos(){
	var TS=ExpYes?FLoc.document.body.scrollTop:FLoc.pageYOffset;
	if(TS!=StaticPos){
		var FCSt=Nav4?FrstCntnr:FrstCntnr.style;
		FrstCntnr.OrgTop=StartTop+TS;FCSt.top=FrstCntnr.OrgTop+P_X;
		StaticPos=TS}}

function ClcJus(){
	var a=BorderBtwnElmnts?1:2,b=BorderBtwnElmnts?BorderWidth:0,Sz=Math.round(((PartOfWindow*FWinW-a*BorderWidth)/NoOffFirstLineMenus)-b),i,j;
	for(i=1;i<NoOffFirstLineMenus+1;i++){j=eval("Menu"+i);j[5]=Sz}
	StartLeft=0}

function ClcTrgt(){
	var TLoc=Nav4?FLoc.document.layers[TargetLoc]:DomYes?FLoc.document.getElementById(TargetLoc):FLoc.document.all[TargetLoc];
	StartTop=M_StrtTp;
	StartLeft=M_StrtLft;
	if(DomYes){while(TLoc){StartTop+=TLoc.offsetTop;StartLeft+=TLoc.offsetLeft;TLoc=TLoc.offsetParent}}
	else{StartTop+=Nav4?TLoc.pageY:TLoc.offsetTop;StartLeft+=Nav4?TLoc.pageX:TLoc.offsetLeft}}

function ClcLft(){
	if(MenuCentered.indexOf("left")==-1){
		var Sz=FWinW-(!Nav4?parseInt(FrstCntnr.style.width):FrstCntnr.clip.width);
		StartLeft=M_StrtLft;StartLeft+=MenuCentered.indexOf("right")!=-1?Sz:Sz/2;
		if(StartLeft<0)StartLeft=0}}

function ClcTp(){
	if(MenuVerticalCentered!="top"&&MenuVerticalCentered!="static"){
		var Sz=FWinH-(!Nav4?parseInt(FrstCntnr.style.height):FrstCntnr.clip.height);
		StartTop=M_StrtTp;StartTop+=MenuVerticalCentered=="bottom"?Sz:Sz/2;
		if(StartTop<0)StartTop=0}}

function PosMenu(Ct,Tp,Lt){
	var Ti,Li,Hi,Mb=Ct.FrstMbr,CStl=!Nav4?Ct.style:Ct,MStl=!Nav4?Mb.style:Mb,PadL=Mb.value.indexOf("<")==-1?LftXtra:0,PadT=Mb.value.indexOf("<")==-1?TpXtra:0,MWt=!Nav4?parseInt(MStl.width)+PadL:MStl.clip.width,MHt=!Nav4?parseInt(MStl.height)+PadT:MStl.clip.height,CWt=!Nav4?parseInt(CStl.width):CStl.clip.width,CHt=!Nav4?parseInt(CStl.height):CStl.clip.height,CCw,CCh,STp,SLt;
	RLvl++;
	if(RLvl==1&&AcrssFrms)!MenuFramesVertical?Tp=FWinH-CHt+(Nav4?MacCom?-2:4:0):Lt=FWinW-CWt+(Nav4?MacCom?-2:4:0);
	if(RLvl==2&&AcrssFrms)!MenuFramesVertical?Tp=0:Lt=0;
	if(RLvl==2&&AcrssFrms){Tp+=VerCorrect;Lt+=HorCorrect}
	CStl.top=RLvl==1?Tp+P_X:0;Ct.OrgTop=Tp;
	CStl.left=RLvl==1?Lt+P_X:0;	Ct.OrgLeft=Lt;
	if(RLvl==1&&FirstLineHorizontal){Hi=1;Li=CWt-MWt-2*BorderWidth;Ti=0}
	else{Hi=Li=0;Ti=CHt-MHt-2*BorderWidth}
	while(Mb!=null){
		MStl.left=Li+BorderWidth+P_X;MStl.top=Ti+BorderWidth+P_X;
		if(Nav4)Mb.CLyr.moveTo(Li+BorderWidth,Ti+BorderWidth);
		if(Mb.CCn){if(Hi){STp=Ti+MHt+2*BorderWidth;SLt=Li}
			else{SLt=Li+(1-ChildOverlap)*MWt+BorderWidth;STp=RLvl==1&&AcrssFrms?Ti:Ti+ChildVerticalOverlap*MHt}
			PosMenu(Mb.CCn,STp,SLt)}
		Mb=Mb.PrvMbr;
		if(Mb){	MStl=!Nav4?Mb.style:Mb;PadL=Mb.value.indexOf("<")==-1?LftXtra:0;
			PadT=Mb.value.indexOf("<")==-1?TpXtra:0;
			MWt=!Nav4?parseInt(MStl.width)+PadL:MStl.clip.width;
			MHt=!Nav4?parseInt(MStl.height)+PadT:MStl.clip.height;
			Hi?Li-=BorderBtwnElmnts?(MWt+BorderWidth):(MWt):Ti-=BorderBtwnElmnts?(MHt+BorderWidth):MHt}}
	RLvl--}

function Initiate(){
	if(IniFlg){Init(FrstCntnr);IniFlg=0;if(ShwFlg)AfterCloseAll();ShwFlg=0}}

function Init(CPt){
	var Mb=CPt.FrstMbr,MCSt=Nav4?CPt:CPt.style;
	RLvl++;
	MCSt.visibility=RLvl==1?M_Show:M_Hide;
	CPt.Shw=RLvl==1?1:0;
	while(Mb!=null){
		if(Mb.Hilite){Mb.Hilite=0;
			if(LowBgColor)Nav4?Mb.bgColor=LowBgColor:Mb.style.backgroundColor=LowBgColor;
			if(Nav4){if(Mb.value.indexOf("<img")==-1){Mb.document.write(Mb.value);Mb.document.close()}}
			else Mb.style.color=FontLowColor}
		if(Mb.CCn)Init(Mb.CCn);
		Mb=Mb.PrvMbr}
	RLvl--}

function ClrAllChlds(Pt){
	var PSt,Pc;
	while(Pt){	if(Pt.Hilite){
			Pc=Nav4?Pt.CLyr:Pt;
			if(Pc!=CurOvr){
				Pt.Hilite=0;
				if(LowBgColor)Nav4?Pt.bgColor=LowBgColor:Pt.style.backgroundColor=LowBgColor;
				if(Nav4){if(Pt.value.indexOf("<img")==-1){Pt.document.write(Pt.value);Pt.document.close()}}
				else Pt.style.color=FontLowColor}
			if(Pt.CCn){	PSt=Nav4?Pt.CCn:Pt.CCn.style;
				if(Pc!=CurOvr){PSt.visibility=M_Hide;Pt.CCn.Shw=0}
				ClrAllChlds(Pt.CCn.FrstMbr)}
			break}
	Pt=Pt.PrvMbr}}

function GoTo(){
	var HP=Nav4?this.LLyr:this;
	if(HP.Arr[1]){
		HP.Hilite=0;
		if(LowBgColor)Nav4?HP.bgColor=LowBgColor:HP.style.backgroundColor=LowBgColor;
		if(Nav4){if(HP.value.indexOf("<img")==-1){HP.document.write(HP.value);HP.document.close()}}
		else HP.style.color=FontLowColor;
		IniFlg=1;Initiate();
		HP.Arr[1].indexOf("javascript:")!=-1?eval(HP.Arr[1]):DcLoc.location.href=BaseHref+HP.Arr[1]}}

function HiliteItem(P){
	if(HighBgColor)Nav4?P.bgColor=HighBgColor:P.style.backgroundColor=HighBgColor;
	if(Nav4){if(P.value.indexOf("<img")==-1){P.document.write(P.Ovalue);P.document.close()}}
	else P.style.color=FontHighColor;
	P.Hilite=1}

function OpenMenu(){
	if(!Ldd||!Crtd)return;
	var TS=ExpYes?ScLoc.document.body.scrollTop:ScLoc.pageYOffset,LS=ExpYes?ScLoc.document.body.scrollLeft:ScLoc.pageXOffset,CCnt=Nav4?this.LLyr.CCn:this.CCn,THt=Nav4?this.clip.height:parseInt(this.style.height),TWt=Nav4?this.clip.width:parseInt(this.style.width),TLt=AcrssFrms&&this.Lvl==1&&!FirstLineHorizontal?0:Nav4?this.Ctnr.left:parseInt(this.Ctnr.style.left),TTp=AcrssFrms&&this.Lvl==1&&FirstLineHorizontal?0:Nav4?this.Ctnr.top:parseInt(this.Ctnr.style.top),HP=Nav4?this.LLyr:this;
	CurOvr=this;
	IniFlg=0;
	ClrAllChlds(this.Ctnr.FrstMbr);
	if(!HP.Hilite){
		if(HighBgColor)Nav4?HP.bgColor=HighBgColor:HP.style.backgroundColor=HighBgColor;
		if(Nav4){if(HP.value.indexOf("<img")==-1){HP.document.write(HP.Ovalue);HP.document.close()}}
		else HP.style.color=FontHighColor;
		HP.Hilite=1}
	if(CCnt!=null&&!CCnt.Shw){
		if(!ShwFlg){ShwFlg=1;	BeforeFirstOpen()}
		var CCW=Nav4?this.LLyr.CCn.clip.width:parseInt(this.CCn.style.width),CCH=Nav4?this.LLyr.CCn.clip.height:parseInt(this.CCn.style.height),CCSt=Nav4?this.LLyr.CCn:this.CCn.style,SLt=AcrssFrms&&this.Lvl==1?CCnt.OrgLeft+TLt+LS:CCnt.OrgLeft+TLt,STp=AcrssFrms&&this.Lvl==1?CCnt.OrgTop+TTp+TS:CCnt.OrgTop+TTp;
		if(SLt+CCW>SWinW+LS)SLt=this.Lvl==1?SWinW+LS-CCW:SLt-(CCW+(1-2*ChildOverlap)*TWt);
		if(SLt<LS)SLt=LS;
		if(STp+CCH>TS+SWinH)STp=this.Lvl==1?STp=TS+SWinH-CCH:STp-CCH+(1-2*ChildVerticalOverlap)*THt;
		if(STp<TS)STp=TS;
		CCSt.top=STp+P_X;CCSt.left=SLt+P_X;
		if(Exp6Plus&&MenuSlide){this.CCn.filters[0].Apply();this.CCn.filters[0].play()}
		CCSt.visibility=M_Show}}

function OpenMenuClick(){
	if(!Ldd||!Crtd)return;
	var HP=Nav4?this.LLyr:this;CurOvr=this;
	IniFlg=0;
	ClrAllChlds(this.Ctnr.FrstMbr);
	if(HighBgColor)Nav4?HP.bgColor=HighBgColor:HP.style.backgroundColor=HighBgColor;
	if(Nav4){if(HP.value.indexOf("<img")==-1){HP.document.write(HP.Ovalue);HP.document.close()}}
	else HP.style.color=FontHighColor;
	HP.Hilite=1}

function CloseMenu(){
	if(!Ldd||!Crtd)return;
	if(this==CurOvr){
		IniFlg=1;
		if(CloseTmr)clearTimeout(CloseTmr);
		CloseTmr=setTimeout("Initiate(CurOvr)",DissapearDelay)}}

function CntnrSetUp(W,H,NoOff,WMu,Mc){
	this.FrstMbr=null;
	this.NrItms=NoOff;
	this.Shw=0;
	this.OrgLeft=this.OrgTop=0;
	if(Nav4){	if(BorderColor)this.bgColor=BorderColor;this.visibility=M_Hide;this.resizeTo(W,H)}
	else{	if(BorderColor)this.style.backgroundColor=BorderColor;
		this.style.width=W+P_X;this.style.height=H+P_X;
		if(Exp6Plus){FStr="";if(MenuSlide&&RLvl!=1)FStr=MenuSlide;
			if(MenuShadow)FStr+=MenuShadow;if(MenuOpacity)FStr+=MenuOpacity;
			if(FStr!="")this.style.filter=FStr}}}

function MbrSetUp(MbC,PrMmbr,WMu,Wd,Ht){
	var Lctn=RLvl==1?FLoc:ScLoc,Tfld=this.Arr[0],t,T,L,W,H,S,a;
	this.PrvMbr=PrMmbr;this.Lvl=RLvl;this.Ctnr=MbC;
	this.CCn=null;this.ai=null;this.Hilite=0;
	this.style.overflow=M_Hide;
	this.style.cursor=ExpYes&&(this.Arr[1]||(RLvl==1&&UnfoldsOnClick))?"hand":"default";
	this.style.cursor=(this.Arr[1]||(RLvl==1&&UnfoldsOnClick))?ExpYes?"hand":"pointer":"default";
	this.value=Tfld;
	this.style.color=FontLowColor;
	this.style.fontFamily=FontFamily;
	this.style.fontSize=!Mac4?FontSize+"pt":Math.round(4*FontSize)/3+"pt";
	this.style.fontWeight=FontBold?"bold":"normal";
	this.style.fontStyle=FontItalic?"italic":"normal";
	if(LowBgColor)this.style.backgroundColor=LowBgColor;
	this.style.textAlign=MenuTextCentered;
	if(this.Arr[2])this.style.backgroundImage="url(\""+this.Arr[2]+"\")";
	if(Tfld.indexOf("<img")==-1){
		this.style.width=Wd-LftXtra+P_X;this.style.height=Ht-TpXtra+P_X;
		this.style.paddingLeft=LeftPaddng+P_X;this.style.paddingTop=TopPaddng+P_X}
	else{	this.style.width=Wd+P_X;this.style.height=Ht+P_X}
	if(Tfld.indexOf("<")==-1&&DomYes){
		t=Lctn.document.createTextNode(Tfld);this.appendChild(t)}
	else this.innerHTML=Tfld;
	if(this.Arr[3]){
		a=RLvl==1&&FirstLineHorizontal?3:0;
		if(Arrws[a]!=""){S=Arrws[a];W=Arrws[a+1];H=Arrws[a+2];
			T=RLvl==1&&FirstLineHorizontal?Ht-H-2:(Ht-H)/2;L=Wd-W-2;
			if(DomYes){
				t=Lctn.document.createElement("img");this.appendChild(t);
				t.style.position="absolute";t.src=S;t.style.width=W+P_X;t.style.height=H+P_X;
				t.style.top=T+P_X;t.style.left=L+P_X}
			else{	Tfld+="<div id=\""+WMu+"_im\" style=\"position:absolute; top:"+T+"; left:"+L+"; width:"+W+"; height:"+H+";visibility:inherit\"><img src=\""+S+"\"></div>";
				this.innerHTML=Tfld;t=Lctn.document.all[WMu+"_im"]}
			this.ai=t}}
	if(ExpYes){this.onselectstart=CnclSlct;
		this.onmouseover=RLvl==1&&UnfoldsOnClick?OpenMenuClick:OpenMenu;
		this.onmouseout=CloseMenu;
		this.onclick=RLvl==1&&UnfoldsOnClick&&this.Arr[3]?OpenMenu:GoTo}
	else{	RLvl==1&&UnfoldsOnClick?this.addEventListener("mouseover",OpenMenuClick,false):this.addEventListener("mouseover",OpenMenu,false);
		this.addEventListener("mouseout",CloseMenu,false);
		RLvl==1&&UnfoldsOnClick&&this.Arr[3]?this.addEventListener("click",OpenMenu,false):this.addEventListener("click",GoTo,false)}}

function NavMbrSetUp(MbC,PrMmbr,WMu,Wd,Ht){
	var a;
	this.value=this.Arr[0];
	CntrTxt=MenuTextCentered!="left"?"<div align=\""+MenuTextCentered+"\">":"";
	TxtClose="</font>"+MenuTextCentered!="left"?"</div>":"";
	if(LeftPaddng&&this.value.indexOf("<img")==-1&&MenuTextCentered=="left")this.value="&nbsp\;"+this.value;
	if(FontBold)this.value=this.value.bold();
	if(FontItalic)this.value=this.value.italics();
	this.Ovalue=this.value;
	this.value=this.value.fontcolor(FontLowColor);
	this.Ovalue=this.Ovalue.fontcolor(FontHighColor);
	this.value=CntrTxt+"<font face=\""+FontFamily+"\" point-size=\""+(!Mac4?FontSize:Math.round(4*FontSize)/3)+"\">"+this.value+TxtClose;
	this.Ovalue=CntrTxt+"<font face=\""+FontFamily+"\" point-size=\""+(!Mac4?FontSize:Math.round(4*FontSize)/3)+"\">"+this.Ovalue+TxtClose;
	this.CCn=null;this.PrvMbr=PrMmbr;this.Hilite=0;this.visibility="inherit";
	if(LowBgColor)this.bgColor=LowBgColor;
	this.resizeTo(Wd,Ht);
	if(!AcrssFrms&&this.Arr[2])this.background.src=this.Arr[2];
	this.document.write(this.value);this.document.close();
	this.CLyr=new Layer(Wd,MbC);
	this.CLyr.Lvl=RLvl;
	this.CLyr.visibility="inherit";
	this.CLyr.onmouseover=RLvl==1&&UnfoldsOnClick?OpenMenuClick:OpenMenu;
	this.CLyr.onmouseout=CloseMenu;
	this.CLyr.captureEvents(Event.MOUSEUP);
	this.CLyr.onmouseup=RLvl==1&&UnfoldsOnClick&&this.Arr[3]?OpenMenu:GoTo;
	this.CLyr.LLyr=this;
	this.CLyr.resizeTo(Wd,Ht);
	this.CLyr.Ctnr=MbC;
	if(this.Arr[3]){
		a=RLvl==1&&FirstLineHorizontal?3:0;
		if(Arrws[a]!=""){
			this.CLyr.ILyr=new Layer(Arrws[a+1],this.CLyr);
			this.CLyr.ILyr.visibility="inherit";
			this.CLyr.ILyr.top=RLvl==1&&FirstLineHorizontal?Ht-Arrws[a+2]-2:(Ht-Arrws[a+2])/2;
			this.CLyr.ILyr.left=Wd-Arrws[a+1]-2;
			this.CLyr.ILyr.width=Arrws[a+1];this.CLyr.ILyr.height=Arrws[a+2];
			ImgStr="<img src=\""+Arrws[a]+"\" width=\""+Arrws[a+1]+"\" height=\""+Arrws[a+2]+"\">";
			this.CLyr.ILyr.document.write(ImgStr);this.CLyr.ILyr.document.close()}}}

function CreateMenuStructure(MNm,No,Mcllr){
	RLvl++;
	var i,NOs,Mbr,W=0,H=0,PMb=null,WMnu=MNm+"1",MWd=eval(WMnu+"[5]"),MHt=eval(WMnu+"[4]"),Lctn=RLvl==1?FLoc:ScLoc;
	if(RLvl==1&&FirstLineHorizontal){
		for(i=1;i<No+1;i++){WMnu=MNm+eval(i);W=eval(WMnu+"[5]")?W+eval(WMnu+"[5]"):W+MWd}
		W=BorderBtwnElmnts?W+(No+1)*BorderWidth:W+2*BorderWidth;H=MHt+2*BorderWidth}
	else{	for(i=1;i<No+1;i++){WMnu=MNm+eval(i);H=eval(WMnu+"[4]")?H+eval(WMnu+"[4]"):H+MHt}
		H=BorderBtwnElmnts?H+(No+1)*BorderWidth:H+2*BorderWidth;W=MWd+2*BorderWidth}
	if(DomYes){
		var MbC=Lctn.document.createElement("div");
		MbC.style.position="absolute";MbC.style.visibility=M_Hide;
		Lctn.document.body.appendChild(MbC)}
	else{	if(Nav4)var MbC=new Layer(W,Lctn);
		else{	WMnu+="c";
			Lctn.document.body.insertAdjacentHTML("AfterBegin","<div id=\""+WMnu+"\" style=\"visibility:hidden; position:absolute;\"><\/div>");
			var MbC=Lctn.document.all[WMnu]}}
	MbC.SetUp=CntnrSetUp;MbC.SetUp(W,H,No,MNm+"1",Mcllr);
	if(Exp4){	MbC.InnerString="";
		for(i=1;i<No+1;i++){WMnu=MNm+eval(i);MbC.InnerString+="<div id=\""+WMnu+"\" style=\"position:absolute;\"><\/div>"}
		MbC.innerHTML=MbC.InnerString}
	for(i=1;i<No+1;i++){
		WMnu=MNm+eval(i);NOs=eval(WMnu+"[3]");
		W=RLvl==1&&FirstLineHorizontal?eval(WMnu+"[5]")?eval(WMnu+"[5]"):MWd:MWd;
		H=RLvl==1&&FirstLineHorizontal?MHt:eval(WMnu+"[4]")?eval(WMnu+"[4]"):MHt;
		if(DomYes){Mbr=Lctn.document.createElement("div");Mbr.style.position="absolute";Mbr.style.visibility="inherit";MbC.appendChild(Mbr)}
		else Mbr=Nav4?new Layer(W,MbC):Lctn.document.all[WMnu];
		Mbr.Arr=eval(WMnu);
		Mbr.SetUp=Nav4?NavMbrSetUp:MbrSetUp;Mbr.SetUp(MbC,PMb,WMnu,W,H);
		if(NOs)Mbr.CCn=CreateMenuStructure(WMnu+"_",NOs,Mbr);
		PMb=Mbr}
	MbC.FrstMbr=Mbr;
	RLvl--;
	return(MbC)}

function CreateMenuStructureAgain(MNm,No){
	var i,WMnu,NOs,PMb,Mbr=FrstCntnr.FrstMbr;RLvl++;
	for(i=No;i>0;i--){WMnu=MNm+eval(i);NOs=eval(WMnu+"[3]");PMb=Mbr;
		if(NOs)Mbr.CCn=CreateMenuStructure(WMnu+"_",NOs,Mbr);
		Mbr=Mbr.PrvMbr}
	RLvl--}