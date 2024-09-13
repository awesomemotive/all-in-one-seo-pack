/** @module config/transitionWords */

const singleWords = [ 'aquí', 'abajo', 'además', 'adicional', 'ahí', 'alrededor', 'anteriormente', 'arriba', 'así', 'asimismo',
	'aún', 'aunque', 'cerca', 'ciertamente', 'como', 'concluyendo', 'conque', 'contrariamente', 'cuando', 'debajo', 'decididamente',
	'decisivamente', 'después', 'desde', 'detrás', 'diferentemente', 'dónde', 'durante', 'efectivamente', 'entonces', 'entre', 'especialmente',
	'específicamente', 'eventualmente', 'evidentemente', 'excepto', 'finalmente', 'frecuentemente', 'generalmente', 'igualmente', 'justamente',
	'lógicamente', 'luego', 'mas', 'mientras', 'no', 'o', 'para', 'pero', 'por', 'porque', 'posteriormente', 'primero', 'principalmente',
	'probablemente', 'pronto', 'próximamente', 'pues', 'raramente', 'realmente', 'seguidamente', 'segundo', 'semejantemente', 'si', 'siguiente',
	'sino', 'súbitamente', 'supongamos', 'también', 'tampoco', 'tercero', 'verbigracia', 'vice-versa', 'y', 'ya' ]
const multipleWords = [ 'a causa de', 'a continuación', 'a diferencia de', 'a fin de cuentas', 'a la inversa', 'a la misma vez',
	'a la izquierda', 'a la derecha', 'a más de', 'a más de esto', 'a menos que', 'a no ser que', 'a partir de', 'a pesar de',
	'a pesar de eso', 'a pesar de todo', 'a peser de', 'a propósito', 'a saber', 'a todo esto', 'ahora bien', 'a través de', 'aun así', 'aún así', 'aún más',
	'al contrario', 'al fin y al cabo', 'al final', 'al inicio', 'al mismo tiempo', 'al principio', 'ante todo', 'antes bien',
	'antes de', 'antes de nada', 'antes que nada', 'aparte de', 'aquí y allá', 'as así como', 'así como', 'así mismo', 'así pues', 'así que', 'así y todo', 'claro está que',
	'claro que', 'claro que sí', 'como caso típico', 'como decíamos', 'como era de esperar', 'como es de esperar', 'como muestra', 'como resultado',
	'como se ha notado', 'como sigue', 'comparado con', 'con el objeto de', 'con el propósito de', 'con que', 'con relación a', 'con tal de que',
	'con todo', 'dado que', 'de ahí', 'de cierta manera', 'de cualquier manera', 'de cualquier modo', 'de ello resulta que', 'de esta forma',
	'de este modo', 'de golpe', 'de hecho', 'de igual forma', 'de igual manera', 'de igual modo', 'de igualmanera', 'de la manera siguiente', 'de la misma forma',
	'de la misma manera', 'de manera semejante', 'de modo que', 'de nuevo', 'de otra manera', 'de otro modo', 'de pronto', 'de qualquier manera',
	'de repente', 'de suerte que', 'de tal modo', 'de todas formas', 'de todas maneras', 'de todos modos', 'de veras', 'debido a',
	'debido a que', 'del mismo modo', 'dentro de poco', 'desde entonces', 'después de', 'después de todo', 'ejemplo de esto', 'el caso es que',
	'en aquel tiempo', 'en cambio', 'en cierto modo', 'en comparación con', 'en conclusión', 'en concreto', 'en conformidad con', 'en consecuencia',
	'en consiguiente', 'en contraste', 'en contraste con', 'en cualquier caso', 'en cuanto', 'en cuanto a', 'en definitiva', 'en efecto',
	'en el caso de', 'en el caso de que', 'en el centro de', 'en el medio', 'en este sentido', 'en fin', 'en fin de cuentas', 'en frente de', 'en general',
	'en lugar de', 'en otras palabras', 'en otro orden', 'en otros términos', 'en particular', 'en primer lugar', 'en primer plano',
	'en primer término', 'en primera instancia', 'en realidad', 'en relación a', 'en relación con', 'en representación de', 'en resumen',
	'en resumidas cuentas', 'en segundo lugar', 'en seguida', 'en síntesis', 'en suma', 'en tanto que', 'en todo caso', 'en último término',
	'en verdad', 'en vez de', 'en virtud de', 'entre ellas figura', 'entre ellos figura', 'es cierto que', 'es decir', 'es evidente que',
	'es incuestionable', 'es indudable', 'es más', 'es por ello', 'está claro que', 'esto indica', 'excepto si', 'generalmente por ejemplo',
	'gracias a', 'hasta aquí', 'hasta cierto punto', 'hasta el momento', 'hay que añadir', 'igual que', 'la mayor parte del tiempo',
	'la mayoría del tiempo', 'lo que es peor', 'más allá', 'más bien', 'más tarde', 'mejor dicho', 'mientras que', 'mientras tanto', 'mirándolo todo',
	'nadie puede ignorar', 'no faltaría más', 'no obstante', 'o sea', 'otra vez', 'otro aspecto', 'par ilustrar', 'para concluir', 'para conclusión',
	'para continuar', 'para empezar', 'para finalizar', 'para mencionar una cosa', 'para que', 'para resumir', 'para terminar', 'parecido a',
	'pongamos por caso', 'por añadidura', 'por cierto', 'por consiguiente', 'por ejemplo', 'por el consiguiente', 'por el contrario',
	'por el hecho que', 'por eso', 'por esta razón', 'por esto', 'por esto mismo', 'por fin', 'por la mayor parte', 'por lo general',
	'por lo que', 'por lo tanto', 'por otra parte', 'por otro lado', 'por supuesto', 'por tanto', 'por último', 'por un lado', 'por una parte',
	'primero que nada', 'primero que todo', 'pues bien', 'puesto que', 'rara vez', 'resulta que', 'sea como sea', 'seguidamente entre tanto',
	'si bien', 'siempre que', 'siempre y cuando', 'siguiendo con', 'sigue que', 'sin duda', 'sin embargo', 'sin ir más lejos', 'sobre todo',
	'supuesto que', 'tal como', 'tal vez', 'tales como', 'tan pronto como', 'tanto como', 'una vez', 'ya que' ]

/**
 * Returns lists with transition words to be used by the assessments.
 * @returns {Object} The object with transition word lists.
 */
export default function () {
	return {
		singleWords   : singleWords,
		multipleWords : multipleWords,
		allWords      : singleWords.concat(multipleWords)
	}
}