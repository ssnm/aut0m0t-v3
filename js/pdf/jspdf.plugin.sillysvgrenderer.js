;(function(jsPDFAPI){'use strict'
jsPDFAPI.addSVG=function(svgtext,x,y,w,h){var undef
if(x===undef||y===undef){throw new Error("addSVG needs values for 'x' and 'y'");}function InjectCSS(cssbody,document){var styletag=document.createElement('style');styletag.type='text/css';if(styletag.styleSheet){styletag.styleSheet.cssText=cssbody;}else{styletag.appendChild(document.createTextNode(cssbody));}document.getElementsByTagName("head")[0].appendChild(styletag);}function createWorkerNode(document){var frameID='childframe',frame=document.createElement('iframe')
InjectCSS('.jsPDF_sillysvg_iframe {display:none;position:absolute;}',document)
frame.name=frameID
frame.setAttribute("width",0)
frame.setAttribute("height",0)
frame.setAttribute("frameborder","0")
frame.setAttribute("scrolling","no")
frame.setAttribute("seamless","seamless")
frame.setAttribute("class","jsPDF_sillysvg_iframe")
document.body.appendChild(frame)
return frame}function attachSVGToWorkerNode(svgtext,frame){var framedoc=(frame.contentWindow||frame.contentDocument).document
framedoc.write(svgtext)
framedoc.close()
return framedoc.getElementsByTagName('svg')[0]}function convertPathToPDFLinesArgs(path){'use strict'
var x=parseFloat(path[1]),y=parseFloat(path[2]),vectors=[],position=3,len=path.length
while(position<len){if(path[position]==='c'){vectors.push([parseFloat(path[position+1]),parseFloat(path[position+2]),parseFloat(path[position+3]),parseFloat(path[position+4]),parseFloat(path[position+5]),parseFloat(path[position+6])])
position+=7}else if(path[position]==='l'){vectors.push([parseFloat(path[position+1]),parseFloat(path[position+2])])
position+=3}else{position+=1}}return[x,y,vectors]}var workernode=createWorkerNode(document),svgnode=attachSVGToWorkerNode(svgtext,workernode),scale=[1,1],svgw=parseFloat(svgnode.getAttribute('width')),svgh=parseFloat(svgnode.getAttribute('height'))
if(svgw&&svgh){if(w&&h){scale=[w/svgw,h/svgh]}else if(w){scale=[w/svgw,w/svgw]}else if(h){scale=[h/svgh,h/svgh]}}var i,l,tmp,linesargs,items=svgnode.childNodes
for(i=0,l=items.length;i<l;i++){tmp=items[i]
if(tmp.tagName&&tmp.tagName.toUpperCase()==='PATH'){linesargs=convertPathToPDFLinesArgs(tmp.getAttribute("d").split(' '))
linesargs[0]=linesargs[0]*scale[0]+x
linesargs[1]=linesargs[1]*scale[1]+y
this.lines.call(this,linesargs[2],linesargs[0],linesargs[1],scale)}}return this}})(jsPDF.API);