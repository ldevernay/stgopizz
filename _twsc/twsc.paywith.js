PW_LANGUAGECODE=_("FR");PW_ORDERLABEL=_("Numéro de commande: {OrderID}");function PW_RemoveAccents(_0xD6c){return _0xD6c.replace(/[Ã Ã¡Ã¢Ã£Ã¤Ã¥]/gi,'a').replace(/[Ã§]/gi,'c').replace(/[Ã¨Ã©ÃªÃ«]/gi,'e').replace(/[Ã¬Ã­Ã®Ã¯]/gi,'i').replace(/[Ã±]/gi,'n').replace(/[Ã°Ã²Ã³Ã´ÃµÃ¶]/gi,'o').replace(/[Ã¹ÃºÃ»Ã¼]/gi,'u').replace(/[Ã½Ã¿]/gi,'y').replace(/[Ã€ÃÃ‚ÃƒÃ„Ã…]/gi,'A').replace(/[Ã‡]/gi,'C').replace(/[ÃˆÃ‰ÃŠÃ‹]/gi,'E').replace(/[ÃŒÃÃŽÃ]/gi,'I').replace(/[Ã‘]/gi,'N').replace(/[Ã’Ã“Ã”Ã•Ã–]/gi,'O').replace(/[Ã™ÃšÃ›Ãœ]/gi,'U').replace(/[ÃÅ¸]/gi,'Y');}function PW_ReturnValueFromList(Fkj,_0xCD53,_0x550){var w7NmG=_0xCD53.split('|');var WVrs='';for(var MCp=0;MCp<w7NmG.length;MCp++){var SDJ=w7NmG[MCp].split('^');if(Fkj==SDJ[0]){return(SDJ[SDJ.length-1]);}else if(_0x550&&_0x550==SDJ[0])WVrs=SDJ[SDJ.length-1];}if(!_0x550)return('');return(WVrs);}function PW_BuildPostParam($775a,mf2P,jZcrZ,$b5BE8){if(!jZcrZ)return("");if($775a)return('<input type="hidden" name="'+mf2P+'" value="'+jZcrZ+'" />');return(($b5BE8?'':'&')+mf2P+'='+encodeURIComponent(jZcrZ));}function PW_ClearJSstring(_0xD6c){return _0xD6c.replace(/"/gi,'\'');}function PW_GetOrderLabel(){var _0xD6c=PW_ClearJSstring(PW_ORDERLABEL);return _0xD6c.replace(_("{OrderId}"),AsTQ());}