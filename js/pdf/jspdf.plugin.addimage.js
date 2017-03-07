;(function(jsPDFAPI){'use strict'
var namespace='addImage_',supported_image_types=['jpeg','jpg','png'];var putImage=function(img){var objectNumber=this.internal.newObject(),out=this.internal.write,putStream=this.internal.putStream
img['n']=objectNumber
out('<</Type /XObject')
out('/Subtype /Image')
out('/Width '+img['w'])
out('/Height '+img['h'])
if(img['cs']===this.color_spaces.INDEXED){out('/ColorSpace [/Indexed /DeviceRGB '+(img['pal'].length/3-1)+' '+('smask'in img?objectNumber+2:objectNumber+1)+' 0 R]');}else{out('/ColorSpace /'+img['cs']);if(img['cs']===this.color_spaces.DEVICE_CMYK){out('/Decode [1 0 1 0 1 0 1 0]');}}out('/BitsPerComponent '+img['bpc']);if('f'in img){out('/Filter /'+img['f']);}if('dp'in img){out('/DecodeParms <<'+img['dp']+'>>');}if('trns'in img&&img['trns'].constructor==Array){var trns='',i=0,len=img['trns'].length;for(;i<len;i++)trns+=(img['trns'][i]+' '+img['trns'][i]+' ');out('/Mask ['+trns+']');}if('smask'in img){out('/SMask '+(objectNumber+1)+' 0 R');}out('/Length '+img['data'].length+'>>');putStream(img['data']);out('endobj');if('smask'in img){var dp='/Predictor 15 /Colors 1 /BitsPerComponent '+img['bpc']+' /Columns '+img['w'];var smask={'w':img['w'],'h':img['h'],'cs':'DeviceGray','bpc':img['bpc'],'dp':dp,'data':img['smask']};if('f'in img)smask.f=img['f'];putImage.call(this,smask);}if(img['cs']===this.color_spaces.INDEXED){this.internal.newObject();out('<< /Length '+img['pal'].length+'>>');putStream(this.arrayBufferToBinaryString(new Uint8Array(img['pal'])));out('endobj');}},putResourcesCallback=function(){var images=this.internal.collections[namespace+'images']
for(var i in images){putImage.call(this,images[i])}},putXObjectsDictCallback=function(){var images=this.internal.collections[namespace+'images'],out=this.internal.write,image
for(var i in images){image=images[i]
out('/I'+image['i'],image['n'],'0','R')}},checkCompressValue=function(value){if(value&&typeof value==='string')value=value.toUpperCase();return value in jsPDFAPI.image_compression?value:jsPDFAPI.image_compression.NONE;},getImages=function(){var images=this.internal.collections[namespace+'images'];if(!images){this.internal.collections[namespace+'images']=images={};this.internal.events.subscribe('putResources',putResourcesCallback);this.internal.events.subscribe('putXobjectDict',putXObjectsDictCallback);}return images;},getImageIndex=function(images){var imageIndex=0;if(images){imageIndex=Object.keys?Object.keys(images).length:(function(o){var i=0
for(var e in o){if(o.hasOwnProperty(e)){i++}}return i})(images)}return imageIndex;},notDefined=function(value){return typeof value==='undefined'||value===null;},generateAliasFromData=function(data){return undefined;},doesNotSupportImageType=function(type){return supported_image_types.indexOf(type)===-1;},processMethodNotEnabled=function(type){return typeof jsPDFAPI['process'+type.toUpperCase()]!=='function';},isDOMElement=function(object){return typeof object==='object'&&object.nodeType===1;},createDataURIFromElement=function(element,format){if(element.nodeName==='IMG'&&element.hasAttribute('src')){var src=''+element.getAttribute('src');if(src.indexOf('data:image/')===0)return src;if(!format&&/\.png(?:[?#].*)?$/i.test(src))format='png';}if(element.nodeName==='CANVAS'){var canvas=element;}else{var canvas=document.createElement('canvas');canvas.width=element.clientWidth||element.width;canvas.height=element.clientHeight||element.height;var ctx=canvas.getContext('2d');if(!ctx){throw('addImage requires canvas to be supported by browser.');}ctx.drawImage(element,0,0,canvas.width,canvas.height);}return canvas.toDataURL((''+format).toLowerCase()=='png'?'image/png':'image/jpeg');},checkImagesForAlias=function(imageData,images){var cached_info;if(images){for(var e in images){if(imageData===images[e].alias){cached_info=images[e];break;}}}return cached_info;},determineWidthAndHeight=function(w,h,info){if(!w&&!h){w=-96;h=-96;}if(w<0){w=(-1)*info['w']*72/w/this.internal.scaleFactor;}if(h<0){h=(-1)*info['h']*72/h/this.internal.scaleFactor;}if(w===0){w=h*info['w']/info['h'];}if(h===0){h=w*info['h']/info['w'];}return[w,h];},writeImageToPDF=function(x,y,w,h,info,index,images){var dims=determineWidthAndHeight.call(this,w,h,info),coord=this.internal.getCoordinateString,vcoord=this.internal.getVerticalCoordinateString;w=dims[0];h=dims[1];images[index]=info;this.internal.write('q',coord(w),'0 0',coord(h),coord(x),vcoord(y+h),'cm /I'+info['i'],'Do Q')};jsPDFAPI.color_spaces={DEVICE_RGB:'DeviceRGB',DEVICE_GRAY:'DeviceGray',DEVICE_CMYK:'DeviceCMYK',CAL_GREY:'CalGray',CAL_RGB:'CalRGB',LAB:'Lab',ICC_BASED:'ICCBased',INDEXED:'Indexed',PATTERN:'Pattern',SEPERATION:'Seperation',DEVICE_N:'DeviceN'};jsPDFAPI.decode={DCT_DECODE:'DCTDecode',FLATE_DECODE:'FlateDecode',LZW_DECODE:'LZWDecode',JPX_DECODE:'JPXDecode',JBIG2_DECODE:'JBIG2Decode',ASCII85_DECODE:'ASCII85Decode',ASCII_HEX_DECODE:'ASCIIHexDecode',RUN_LENGTH_DECODE:'RunLengthDecode',CCITT_FAX_DECODE:'CCITTFaxDecode'};jsPDFAPI.image_compression={NONE:'NONE',FAST:'FAST',MEDIUM:'MEDIUM',SLOW:'SLOW'};jsPDFAPI.isString=function(object){return typeof object==='string';};jsPDFAPI.extractInfoFromBase64DataURI=function(dataURI){return/^data:([\w]+?\/([\w]+?));base64,(.+?)$/g.exec(dataURI);};jsPDFAPI.supportsArrayBuffer=function(){return typeof ArrayBuffer!=='undefined'&&typeof Uint8Array!=='undefined';};jsPDFAPI.isArrayBuffer=function(object){if(!this.supportsArrayBuffer())return false;return object instanceof ArrayBuffer;};jsPDFAPI.isArrayBufferView=function(object){if(!this.supportsArrayBuffer())return false;if(typeof Uint32Array==='undefined')return false;return(object instanceof Int8Array||object instanceof Uint8Array||(typeof Uint8ClampedArray!=='undefined'&&object instanceof Uint8ClampedArray)||object instanceof Int16Array||object instanceof Uint16Array||object instanceof Int32Array||object instanceof Uint32Array||object instanceof Float32Array||object instanceof Float64Array);};jsPDFAPI.binaryStringToUint8Array=function(binary_string){var len=binary_string.length;var bytes=new Uint8Array(len);for(var i=0;i<len;i++){bytes[i]=binary_string.charCodeAt(i);}return bytes;};jsPDFAPI.arrayBufferToBinaryString=function(buffer){if(this.isArrayBuffer(buffer))buffer=new Uint8Array(buffer);var binary_string='';var len=buffer.byteLength;for(var i=0;i<len;i++){binary_string+=String.fromCharCode(buffer[i]);}return binary_string;};jsPDFAPI.arrayBufferToBase64=function(arrayBuffer){var base64=''
var encodings='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
var bytes=new Uint8Array(arrayBuffer)
var byteLength=bytes.byteLength
var byteRemainder=byteLength%3
var mainLength=byteLength-byteRemainder
var a,b,c,d
var chunk
for(var i=0;i<mainLength;i=i+3){chunk=(bytes[i]<<16)|(bytes[i+1]<<8)|bytes[i+2]
a=(chunk&16515072)>>18
b=(chunk&258048)>>12
c=(chunk&4032)>>6
d=chunk&63
base64+=encodings[a]+encodings[b]+encodings[c]+encodings[d]}if(byteRemainder==1){chunk=bytes[mainLength]
a=(chunk&252)>>2
b=(chunk&3)<<4
base64+=encodings[a]+encodings[b]+'=='}else if(byteRemainder==2){chunk=(bytes[mainLength]<<8)|bytes[mainLength+1]
a=(chunk&64512)>>10
b=(chunk&1008)>>4
c=(chunk&15)<<2
base64+=encodings[a]+encodings[b]+encodings[c]+'='}return base64};jsPDFAPI.createImageInfo=function(data,wd,ht,cs,bpc,f,imageIndex,alias,dp,trns,pal,smask){var info={alias:alias,w:wd,h:ht,cs:cs,bpc:bpc,i:imageIndex,data:data};if(f)info.f=f;if(dp)info.dp=dp;if(trns)info.trns=trns;if(pal)info.pal=pal;if(smask)info.smask=smask;return info;};jsPDFAPI.addImage=function(imageData,format,x,y,w,h,alias,compression){'use strict'
if(typeof format==='number'){var tmp=h;h=w;w=y;y=x;x=format;format=tmp;}var images=getImages.call(this),dataAsBinaryString;compression=checkCompressValue(compression);if(notDefined(alias))alias=generateAliasFromData(imageData);if(isDOMElement(imageData))imageData=createDataURIFromElement(imageData,format);if(this.isString(imageData)){var base64Info=this.extractInfoFromBase64DataURI(imageData);if(base64Info){format=base64Info[2];imageData=atob(base64Info[3]);}else{if(imgData.charCodeAt(0)===0x89&&imgData.charCodeAt(1)===0x50&&imgData.charCodeAt(2)===0x4e&&imgData.charCodeAt(3)===0x47)format='png';}}format=(format||'JPEG').toLowerCase();if(doesNotSupportImageType(format))throw new Error('addImage currently only supports formats '+supported_image_types+', not \''+format+'\'');if(processMethodNotEnabled(format))throw new Error('please ensure that the plugin for \''+format+'\' support is added');if(this.supportsArrayBuffer()){dataAsBinaryString=imageData;imageData=this.binaryStringToUint8Array(imageData);}var imageIndex=getImageIndex(images),info=checkImagesForAlias(dataAsBinaryString||imageData,images);if(!info)info=this['process'+format.toUpperCase()](imageData,imageIndex,alias,compression,dataAsBinaryString);if(!info)throw new Error('An unkwown error occurred whilst processing the image');writeImageToPDF.call(this,x,y,w,h,info,imageIndex,images);return this};var getJpegSize=function(imgData){'use strict'
var width,height,numcomponents;if(!imgData.charCodeAt(0)===0xff||!imgData.charCodeAt(1)===0xd8||!imgData.charCodeAt(2)===0xff||!imgData.charCodeAt(3)===0xe0||!imgData.charCodeAt(6)==='J'.charCodeAt(0)||!imgData.charCodeAt(7)==='F'.charCodeAt(0)||!imgData.charCodeAt(8)==='I'.charCodeAt(0)||!imgData.charCodeAt(9)==='F'.charCodeAt(0)||!imgData.charCodeAt(10)===0x00){throw new Error('getJpegSize requires a binary string jpeg file')}var blockLength=imgData.charCodeAt(4)*256+imgData.charCodeAt(5);var i=4,len=imgData.length;while(i<len){i+=blockLength;if(imgData.charCodeAt(i)!==0xff){throw new Error('getJpegSize could not find the size of the image');}if(imgData.charCodeAt(i+1)===0xc0||imgData.charCodeAt(i+1)===0xc1||imgData.charCodeAt(i+1)===0xc2||imgData.charCodeAt(i+1)===0xc3||imgData.charCodeAt(i+1)===0xc4||imgData.charCodeAt(i+1)===0xc5||imgData.charCodeAt(i+1)===0xc6||imgData.charCodeAt(i+1)===0xc7){height=imgData.charCodeAt(i+5)*256+imgData.charCodeAt(i+6);width=imgData.charCodeAt(i+7)*256+imgData.charCodeAt(i+8);numcomponents=imgData.charCodeAt(i+9);return[width,height,numcomponents];}else{i+=2;blockLength=imgData.charCodeAt(i)*256+imgData.charCodeAt(i+1)}}},getJpegSizeFromBytes=function(data){var hdr=(data[0]<<8)|data[1];if(hdr!==0xFFD8)throw new Error('Supplied data is not a JPEG');var len=data.length,block=(data[4]<<8)+data[5],pos=4,bytes,width,height,numcomponents;while(pos<len){pos+=block;bytes=readBytes(data,pos);block=(bytes[2]<<8)+bytes[3];if((bytes[1]===0xC0||bytes[1]===0xC2)&&bytes[0]===0xFF&&block>7){bytes=readBytes(data,pos+5);width=(bytes[2]<<8)+bytes[3];height=(bytes[0]<<8)+bytes[1];numcomponents=bytes[4];return{width:width,height:height,numcomponents:numcomponents};}pos+=2;}throw new Error('getJpegSizeFromBytes could not find the size of the image');},readBytes=function(data,offset){return data.subarray(offset,offset+5);};jsPDFAPI.processJPEG=function(data,index,alias,compression,dataAsBinaryString){'use strict'
var colorSpace=this.color_spaces.DEVICE_RGB,filter=this.decode.DCT_DECODE,bpc=8,dims;if(this.isString(data)){dims=getJpegSize(data);return this.createImageInfo(data,dims[0],dims[1],dims[3]==1?this.color_spaces.DEVICE_GRAY:colorSpace,bpc,filter,index,alias);}if(this.isArrayBuffer(data))data=new Uint8Array(data);if(this.isArrayBufferView(data)){dims=getJpegSizeFromBytes(data);data=dataAsBinaryString||this.arrayBufferToBinaryString(data);return this.createImageInfo(data,dims.width,dims.height,dims.numcomponents==1?this.color_spaces.DEVICE_GRAY:colorSpace,bpc,filter,index,alias);}return null;};jsPDFAPI.processJPG=function(){return this.processJPEG.apply(this,arguments);}})(jsPDF.API);