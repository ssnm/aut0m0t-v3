;(function(API){'use strict'
var getCharWidthsArray=API.getCharWidthsArray=function(text,options){if(!options){options={}}var widths=options.widths?options.widths:this.internal.getFont().metadata.Unicode.widths,widthsFractionOf=widths.fof?widths.fof:1,kerning=options.kerning?options.kerning:this.internal.getFont().metadata.Unicode.kerning,kerningFractionOf=kerning.fof?kerning.fof:1
var i,l,char_code,prior_char_code=0,default_char_width=widths[0]||widthsFractionOf,output=[]
for(i=0,l=text.length;i<l;i++){char_code=text.charCodeAt(i)
output.push((widths[char_code]||default_char_width)/widthsFractionOf+(kerning[char_code]&&kerning[char_code][prior_char_code]||0)/kerningFractionOf)
prior_char_code=char_code}return output}
var getArraySum=function(array){var i=array.length,output=0
while(i){;i--;output+=array[i]}return output}
var getStringUnitWidth=API.getStringUnitWidth=function(text,options){return getArraySum(getCharWidthsArray.call(this,text,options))}
var splitLongWord=function(word,widths_array,firstLineMaxLen,maxLen){var answer=[]
var i=0,l=word.length,workingLen=0
while(i!==l&&workingLen+widths_array[i]<firstLineMaxLen){workingLen+=widths_array[i];i++;}answer.push(word.slice(0,i))
var startOfLine=i
workingLen=0
while(i!==l){if(workingLen+widths_array[i]>maxLen){answer.push(word.slice(startOfLine,i))
workingLen=0
startOfLine=i}workingLen+=widths_array[i];i++;}if(startOfLine!==i){answer.push(word.slice(startOfLine,i))}return answer}
var splitParagraphIntoLines=function(text,maxlen,options){if(!options){options={}}var line=[],lines=[line],line_length=options.textIndent||0,separator_length=0,current_word_length=0,word,widths_array,words=text.split(' '),spaceCharWidth=getCharWidthsArray(' ',options)[0],i,l,tmp,lineIndent
if(options.lineIndent===-1){lineIndent=words[0].length+2;}else{lineIndent=options.lineIndent||0;}if(lineIndent){var pad=Array(lineIndent).join(" "),wrds=[];words.map(function(wrd){wrd=wrd.split(/\s*\n/);if(wrd.length>1){wrds=wrds.concat(wrd.map(function(wrd,idx){return(idx&&wrd.length?"\n":"")+wrd;}));}else{wrds.push(wrd[0]);}});words=wrds;lineIndent=getStringUnitWidth(pad,options);}for(i=0,l=words.length;i<l;i++){var force=0;word=words[i]
if(lineIndent&&word[0]=="\n"){word=word.substr(1);force=1;}widths_array=getCharWidthsArray(word,options)
current_word_length=getArraySum(widths_array)
if(line_length+separator_length+current_word_length>maxlen||force){if(current_word_length>maxlen){tmp=splitLongWord(word,widths_array,maxlen-(line_length+separator_length),maxlen)
line.push(tmp.shift())
line=[tmp.pop()]
while(tmp.length){lines.push([tmp.shift()])}current_word_length=getArraySum(widths_array.slice(word.length-line[0].length))}else{line=[word]}lines.push(line)
line_length=current_word_length+lineIndent
separator_length=spaceCharWidth}else{line.push(word)
line_length+=separator_length+current_word_length
separator_length=spaceCharWidth}}if(lineIndent){var postProcess=function(ln,idx){return(idx?pad:'')+ln.join(" ");};}else{var postProcess=function(ln){return ln.join(" ")};}return lines.map(postProcess);}
API.splitTextToSize=function(text,maxlen,options){'use strict'
if(!options){options={}}var fsize=options.fontSize||this.internal.getFontSize(),newOptions=(function(options){var widths={0:1},kerning={}
if(!options.widths||!options.kerning){var f=this.internal.getFont(options.fontName,options.fontStyle),encoding='Unicode'
if(f.metadata[encoding]){return{widths:f.metadata[encoding].widths||widths,kerning:f.metadata[encoding].kerning||kerning}}}else{return{widths:options.widths,kerning:options.kerning}}return{widths:widths,kerning:kerning}}).call(this,options)
var paragraphs
if(Array.isArray(text)){paragraphs=text;}else{paragraphs=text.split(/\r?\n/);}var fontUnit_maxLen=1.0*this.internal.scaleFactor*maxlen/fsize
newOptions.textIndent=options.textIndent?options.textIndent*1.0*this.internal.scaleFactor/fsize:0
newOptions.lineIndent=options.lineIndent;var i,l,output=[]
for(i=0,l=paragraphs.length;i<l;i++){output=output.concat(splitParagraphIntoLines(paragraphs[i],fontUnit_maxLen,newOptions))}return output}
API.myText=function(txt,options,x,y){options=options||{};if(options.align=="center"){var fontSize=this.internal.getFontSize();var pageWidth=this.internal.pageSize.width;var txtWidth=this.getStringUnitWidth(txt)*fontSize/this.internal.scaleFactor;x=(pageWidth-txtWidth)/2;}else if(options.align=="right"){var fontSize=this.internal.getFontSize();var pageWidth=this.internal.pageSize.width;txtWidth=this.getStringUnitWidth(txt)*fontSize/this.internal.scaleFactor;x=(typeof x!="undefined"?((pageWidth-txtWidth)-x):(pageWidth-txtWidth));}this.text(txt,x,y);}})(jsPDF.API);(function(API){})(jsPDF.API);