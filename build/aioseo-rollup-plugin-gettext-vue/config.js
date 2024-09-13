const defaultLanguage = 'en'

const LOCALES_EXT = {
	en : 'en_US'
}

const LOCALEDIRMAP = {
	en : 'locale/en_US/LC_MESSAGES/'
}

const calleeNames = {
	__  : [ '__' ],
	_x  : [ '_x' ],
	_n  : [ '_n' ],
	_nx : [ '_nx' ]
}

const PLURAL = function () {
	const pluralfunc = PLURALFUNC // eslint-disable-line
	const nplurals   = NPLURALS || 2 // eslint-disable-line
	return (pluralfunc)(arguments[nplurals], Array.prototype.slice.call(arguments, 0, nplurals))
}

const calleeFunctions = {
	PLURAL,
	__  : function (text) { return text },
	_x  : function (context, text) { return text },
	_n  : function () { return PLURAL.apply(PLURAL, arguments) },
	_nx : function () { return PLURAL.apply(PLURAL, Array.prototype.slice.call(arguments, 1)) }
}

const extractorOptions = {
	__ : {
		arguments : {
			text       : 0,
			textDomain : 1
		},
		comments : {
			regex            : /^\s*Translators:\s*([\s\S]+)/im,
			otherLineLeading : true,
			sameLineLeading  : false,
			sameLineTrailing : false
		}
	},
	_x : {
		arguments : {
			text       : 0,
			context    : 1,
			textDomain : 2
		},
		comments : {
			regex            : /^\s*Translators:\s*([\s\S]+)/im,
			otherLineLeading : true,
			sameLineLeading  : false,
			sameLineTrailing : false
		}
	},
	_n : {
		arguments : {
			text       : 0,
			textPlural : 1,
			textNumber : 2,
			textDomain : 3
		},
		comments : {
			regex            : /^\s*Translators:\s*([\s\S]+)/im,
			otherLineLeading : true,
			sameLineLeading  : false,
			sameLineTrailing : false
		}
	},
	_nx : {
		arguments : {
			text       : 0,
			textPlural : 1,
			textNumber : 2,
			context    : 3,
			textDomain : 4
		},
		comments : {
			regex            : /^\s*Translators:\s*([\s\S]+)/im,
			otherLineLeading : true,
			sameLineLeading  : false,
			sameLineTrailing : false
		}
	}
}

const optionsArray = [ {
	globalName       : '__aioseo_i18n',
	calleeNames      : calleeNames.__,
	calleeFuncBody   : calleeFunctions.__,
	extractorOptions : extractorOptions.__
}, {
	globalName       : '_x_aioseo_i18n',
	calleeNames      : calleeNames._x,
	calleeFuncBody   : calleeFunctions._x,
	extractorOptions : extractorOptions._x
}, {
	globalName       : '_n_aioseo_i18n',
	calleeNames      : calleeNames._n,
	calleeFuncBody   : calleeFunctions._n,
	extractorOptions : extractorOptions._n
}, {
	globalName       : '_nx_aioseo_i18n',
	calleeNames      : calleeNames._nx,
	calleeFuncBody   : calleeFunctions._nx,
	extractorOptions : extractorOptions._nx
} ]

export default {
	defaultLanguage  : defaultLanguage,
	localesExt       : LOCALES_EXT,
	localedirmap     : LOCALEDIRMAP,
	optionsArray     : optionsArray,
	calleeNames      : calleeNames,
	calleeFunctions  : calleeFunctions,
	extractorOptions : extractorOptions
}