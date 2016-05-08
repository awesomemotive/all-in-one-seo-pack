<?php

/**
 * Checks UTF8 versions
 *
 * @author Michael Torbert.
 * @author Semper Fi Web Design.
 * @copyright http://semperplugins.com
 * @package All-in-One-SEO-Pack
 * @version 1.0.0
 */
$UTF8_TABLES = Array();
$UTF8_TABLES['strtolower'] = array(
	"Ã”Âºâˆ«" 	=> "Ã”Î©Ã¶",
	"Ã”ÂºÏ€" 	=> "Ã”Î©Ã´",
	"Ã”Âºâˆ" 	 => "Ã”Î©Ã²",
	"Ã”Âºâˆ‘" 	=> "Ã”Î©Ã³",
	"Ã”Âºâˆ‚"	=> "Ã”Î©Ã±",
	"Ã”ÂºÂµ" 	=> "Ã”Î©Ã¯",
	"Ã”ÂºÂ¥" 	=> "Ã”Î©Ã®",
	"Ã”Âºâ‰¥" 	=> "Ã”Î©Ã¬",
	"Ã”Âºâ‰¤" 	=> "Ã”Î©Ã­",
	"Ã”ÂºÂ±" 	=> "Ã”Î©Ã«",
	"Ã”Âºâˆž" 	=> "Ã”Î©Ãª",
	"Ã”ÂºÃ˜" 	=> "Ã”Î©Ã¨",
	"Ã”ÂºÃ†" 	=> "Ã”Î©Ã©",
	"Ã”Âºâ‰ " 	=> "Ã”Î©Ã§",
	"Ã”ÂºÂ¨" 	=> "Ã”Î©Ã¥",
	"Ã”ÂºÂ´" 	=> "Ã”Î©Ã£",
	"Ã”Âºâ„¢" 	=> "Ã”Î©Ã¤",
	"Ã”ÂºÂ©" 	=> "Ã”Î©Ã¢",
	"Ã”ÂºÂ®" 	=> "Ã”Î©Ã ",
	"Ã”ÂºÃŸ" 	=> "Ã”Î©Ã¡",
	"Ã”ÂºÂ¶" 	=> "Ã”Î©Ãœ",
	"Ã”Âºâ€¢" 	=> "Ã”Î©Ã–",
	"Ã”ÂºÂ§" 	=> "Ã”Î©Ã‘",
	"Ã”ÂºÂ£" 	=> "Ã”Î©Ã‰",
	"Ã”ÂºÂ¢" 	=> "Ã”Î©Ã‡",
	"Ã”ÂºÂ°" 	=> "Ã”Î©Ã…",
	"â€šÃ‘Â´" 	=> "âˆšâ€¢",
	"â€šÃ‘â„¢" 	=> "k",
	"â€šÃ‘Â¶" 	=> "Å“Ã¢",
	"Â·Ã¸Âª" 	=> "Â·Î©Î©",
	"Â·Ã¸âˆ«" 	=> "Â·Î©Âº",
	"Â·Ã¸Ï€" 	=> "Â·Î©Ï€",
	"Â·Ã¸âˆ" 	 => "Â·Î©âˆ",
	"Â·Ã¸Â¨" 	=> "Â·Ã¸â€¢",
	"Â·Ã¸Â´" 	=> "Â·Î©Âª",
	"Â·Ã¸â„¢" 	=> "Â·Î©âˆ«",
	"Â·Ã¸Â©" 	=> "Â·Ã¸Â°",
	"Â·Ã¸Â®" 	=> "Â·Ã¸ ",
	"Â·Ã¸Ãµ" 	=> "Â·Î©âˆ‘",
	"Â·Ã¸Ã¶" 	=> "Â·Î©âˆ‚",
	"Â·Ã¸Ã´" 	=> "Â·Ã¸Ã«",
	"Â·Ã¸Ã²" 	=> "Â·Ã¸Ãª",
	"Â·Ã¸Ã£" 	=> "Â·Î©Âµ",
	"Â·Ã¸Ã¤" 	=> "Â·Î©Â¥",
	"Â·Ã¸Ã¢" 	=> "Â·Î©â‰¥",
	"Â·Ã¸Ã " 	=> "Â·Î©â‰¤",
	"Â·Ã¦Âª" 	=> "Â·Î©Â±",
	"Â·Ã¦âˆ«" 	=> "Â·Î©âˆž",
	"Â·Ã¦Ï€" 	=> "Â·Ã¦Â±",
	"Â·Ã¦âˆ" 	 => "Â·Ã¦âˆž",
	"Â·Î©Ã˜" 	=> "Â·Î©ÃŸ",
	"Â·Î©Ã†" 	=> "Â·Î©Â¶",
	"Â·Î©â‰ " 	=> "Â·Î©â€¢",
	"Â·Î©Â¨" 	=> "Â·Î©Â§",
	"Â·Î©Â´" 	=> "Â·Î©Â£",
	"Â·Î©â„¢" 	=> "Â·Î©Â¢",
	"Â·Î©Â©" 	=> "Â·Î©Â°",
	"Â·Î©Â®" 	=> "Â·Î© ",
	"Â·Î©Ã¼" 	=> "Â·Î©Ã³",
	"Â·Î©Ã¹" 	=> "Â·Î©Ã¯",
	"Â·Î©Ãµ" 	=> "Â·Î©Ã¬",
	"Â·Î©Ã´" 	=> "Â·Î©Ã«",
	"Â·Î©Ã§" 	=> "Â·Î©Ã–",
	"Â·Î©Ã¥" 	=> "Â·Î©Ã‘",
	"Â·Î©Ã£" 	=> "Â·Î©Ã‰",
	"Â·Î©Ã¤" 	=> "Â·Î©Ã‡",
	"Â·Î©Ã¢" 	=> "Â·Î©Ã…",
	"Â·Î©Ã " 	=> "Â·Î©Ã„",
	"Â·ÂºÃ¸" 	=> "Â·Âºâˆ‘",
	"Â·ÂºÃ¦" 	=> "Â·Âºâˆ‚",
	"Â·ÂºÎ©" 	=> "Â·ÂºÂµ",
	"Â·ÂºÂº" 	=> "Â·ÂºÂ¥",
	"Â·ÂºÂª" 	=> "Â·Âºâ‰¥",
	"Â·Âºâˆ«" 	=> "Â·Âºâ‰¤",
	"Â·ÂºÏ€" 	=> "Â·ÂºÂ±",
	"Â·Âºâˆ" 	 => "Â·Âºâˆž",
	"Â·ÂºÃ˜" 	=> "Â·ÂºÃŸ",
	"Â·ÂºÃ†" 	=> "Â·ÂºÂ¶",
	"Â·Âºâ‰ " 	=> "Â·Âºâ€¢",
	"Â·ÂºÂ¨" 	=> "Â·ÂºÂ§",
	"Â·ÂºÂ´" 	=> "Â·ÂºÂ£",
	"Â·Âºâ„¢" 	=> "Â·ÂºÂ¢",
	"Â·ÂºÂ©" 	=> "Â·ÂºÂ°",
	"Â·ÂºÂ®" 	=> "Â·Âº ",
	"Â·ÂºÃ¹" 	=> "Â·ÂºÃ¯",
	"Â·ÂºÃº" 	=> "Â·ÂºÃ®",
	"Â·ÂºÃµ" 	=> "Â·ÂºÃ¬",
	"Â·ÂºÃ¶" 	=> "Â·ÂºÃ­",
	"Â·ÂºÃ´" 	=> "Â·ÂºÃ«",
	"Â·ÂºÃ²" 	=> "Â·ÂºÃª",
	"Â·ÂºÃ¨" 	=> "Â·ÂºÃ¡",
	"Â·ÂºÃ©" 	=> "Â·ÂºÃœ",
	"Â·ÂºÃ§" 	=> "Â·ÂºÃ–",
	"Â·ÂºÃ¥" 	=> "Â·ÂºÃ‘",
	"Â·ÂºÃ£" 	=> "Â·ÂºÃ‰",
	"Â·ÂºÃ¤" 	=> "Â·ÂºÃ‡",
	"Â·ÂºÃ¢" 	=> "Â·ÂºÃ…",
	"Â·ÂºÃ " 	=> "Â·ÂºÃ„",
	"Â·Âªâˆ" 	 => "Â·ÂªÏ€",
	"Â·Âªâˆ‚" 	=> "Â·Âªâˆ‘",
	"Â·ÂªÂ¥" 	=> "Â·ÂªÂµ",
	"Â·Âªâ‰¤" 	=> "Â·Âªâ‰¥",
	"Â·Âªâˆž" 	=> "Â·ÂªÂ±",
	"Â·ÂªÃ†" 	=> "Â·ÂªÃ˜",
	"Â·ÂªÂ¨" 	=> "Â·Âªâ‰ ",
	"Â·Âªâ„¢" 	=> "Â·ÂªÂ´",
	"Â·ÂªÂ®" 	=> "Â·ÂªÂ©",
	"Â·ÂªÂ¶" 	=> "Â·ÂªÃŸ",
	"Â·ÂªÂ§" 	=> "Â·Âªâ€¢",
	"Â·ÂªÂ¢" 	=> "Â·ÂªÂ£",
	"Â·Âª " 	=> "Â·ÂªÂ°",
	"Â·ÂªÃ»" 	=> "Â·ÂªÃ¼",
	"Â·ÂªÃº" 	=> "Â·ÂªÃ¹",
	"Â·ÂªÃ¶" 	=> "Â·ÂªÃµ",
	"Â·ÂªÃ²" 	=> "Â·ÂªÃ´",
	"Â·ÂªÃ±" 	=> "Â·ÂªÃ³",
	"Â·ÂªÃ®" 	=> "Â·ÂªÃ¯",
	"Â·ÂªÃ­" 	 => "Â·ÂªÃ¬",
	"Â·ÂªÃª" 	=> "Â·ÂªÃ«",
	"Â·ÂªÃ©" 	=> "Â·ÂªÃ¨",
	"Â·ÂªÃ¥" 	=> "Â·ÂªÃ§",
	"Â·ÂªÃ¤" 	=> "Â·ÂªÃ£",
	"Â·ÂªÃ " 	=> "Â·ÂªÃ¢",
	"Â·ÂªÃœ" 	=> "Â·ÂªÃ¡",
	"Â·ÂªÃ‘" 	=> "Â·ÂªÃ–",
	"Â·ÂªÃ‡" 	=> "Â·ÂªÃ‰",
	"Â·ÂªÃ„" 	=> "Â·ÂªÃ…",
	"Â·âˆ«Ã¦" 	=> "Â·âˆ«Ã¸",
	"Â·âˆ«Âº" 	=> "Â·âˆ«Î©",
	"Â·âˆ«âˆ«" 	=> "Â·âˆ«Âª",
	"Â·âˆ«âˆ" 	 => "Â·âˆ«Ï€",
	"Â·âˆ«âˆ‚" 	=> "Â·âˆ«âˆ‘",
	"Â·âˆ«Â¥" 	=> "Â·âˆ«Âµ",
	"Â·âˆ«â‰¤" 	=> "Â·âˆ«â‰¥",
	"Â·âˆ«âˆž" 	=> "Â·âˆ«Â±",
	"Â·âˆ«Ã†" 	=> "Â·âˆ«Ã˜",
	"Â·âˆ«Â¨" 	=> "Â·âˆ«â‰ ",
	"Â·âˆ«â„¢" 	=> "Â·âˆ«Â´",
	"Â·âˆ«Â®" 	=> "Â·âˆ«Â©",
	"Â·âˆ«Â¶" 	=> "Â·âˆ«ÃŸ",
	"Â·âˆ«Â§" 	=> "Â·âˆ«â€¢",
	"Â·âˆ«Â¢" 	=> "Â·âˆ«Â£",
	"Â·âˆ« " 	=> "Â·âˆ«Â°",
	"Â·âˆ«Ã®" 	=> "Â·âˆ«Ã¯",
	"Â·âˆ«Ã­" 	 => "Â·âˆ«Ã¬",
	"Â·âˆ«Ãª" 	=> "Â·âˆ«Ã«",
	"Â·âˆ«Ã©" 	=> "Â·âˆ«Ã¨",
	"Â·âˆ«Ã¥" 	=> "Â·âˆ«Ã§",
	"Â·âˆ«Ã¤" 	=> "Â·âˆ«Ã£",
	"Â·âˆ«Ã " 	=> "Â·âˆ«Ã¢",
	"Â·âˆ«Ãœ" 	=> "Â·âˆ«Ã¡",
	"Â·âˆ«Ã‘" 	=> "Â·âˆ«Ã–",
	"Â·âˆ«Ã‡" 	=> "Â·âˆ«Ã‰",
	"Â·âˆ«Ã„" 	=> "Â·âˆ«Ã…",
	"Â·Ï€Ã¦" 	=> "Â·Ï€Ã¸",
	"Â·Ï€Âº" 	=> "Â·Ï€Î©",
	"Â·Ï€âˆ«" 	=> "Â·Ï€Âª",
	"Â·Ï€âˆ" 	 => "Â·Ï€Ï€",
	"Â·Ï€âˆ‚" 	=> "Â·Ï€âˆ‘",
	"Â·Ï€Â¥" 	=> "Â·Ï€Âµ",
	"Â·Ï€â‰¤" 	=> "Â·Ï€â‰¥",
	"Â·Ï€âˆž" 	=> "Â·Ï€Â±",
	"Â·Ï€Ã†" 	=> "Â·Ï€Ã˜",
	"Â·Ï€Â¨" 	=> "Â·Ï€â‰ ",
	"Â·Ï€â„¢" 	=> "Â·Ï€Â´",
	"Â·Ï€Â®" 	=> "Â·Ï€Â©",
	"Â·Ï€Â¶" 	=> "Â·Ï€ÃŸ",
	"Â·Ï€Â§" 	=> "Â·Ï€â€¢",
	"Â·Ï€Â¢" 	=> "Â·Ï€Â£",
	"Â·Ï€ " 	=> "Â·Ï€Â°",
	"Â·Ï€Ã»" 	=> "Â·Ï€Ã¼",
	"Â·Ï€Ãº" 	=> "Â·Ï€Ã¹",
	"Â·Ï€Ã¶" 	=> "Â·Ï€Ãµ",
	"Â·Ï€Ã²" 	=> "Â·Ï€Ã´",
	"Â·Ï€Ã±" 	=> "Â·Ï€Ã³",
	"Â·Ï€Ã®" 	=> "Â·Ï€Ã¯",
	"Â·Ï€Ã­" 	 => "Â·Ï€Ã¬",
	"Â·Ï€Ãª" 	=> "Â·Ï€Ã«",
	"Â·Ï€Ã©" 	=> "Â·Ï€Ã¨",
	"Â·Ï€Ã¥" 	=> "Â·Ï€Ã§",
	"Â·Ï€Ã¤" 	=> "Â·Ï€Ã£",
	"Â·Ï€Ã " 	=> "Â·Ï€Ã¢",
	"Â·Ï€Ãœ" 	=> "Â·Ï€Ã¡",
	"Â·Ï€Ã‘" 	=> "Â·Ï€Ã–",
	"Â·Ï€Ã‡" 	=> "Â·Ï€Ã‰",
	"Â·Ï€Ã„" 	=> "Â·Ï€Ã…",
	"Â·âˆÃ¦"	 => "Â·âˆÃ¸",
	"Â·âˆÂº" 	 => "Â·âˆÎ©",
	"Â·âˆâˆ«" 	 => "Â·âˆÂª",
	"Â·âˆâˆ"	  => "Â·âˆÏ€",
	"Â·âˆâˆ‚" 	 => "Â·âˆâˆ‘",
	"Â·âˆÂ¥" 	 => "Â·âˆÂµ",
	"Â·âˆâ‰¤" 	 => "Â·âˆâ‰¥",
	"Â·âˆâˆž" 	 => "Â·âˆÂ±",
	"Â·âˆÃ†" 	 => "Â·âˆÃ˜",
	"Â·âˆÂ¨" 	 => "Â·âˆâ‰ ",
	"Â·âˆâ„¢" 	 => "Â·âˆÂ´",
	"Â·âˆÂ®" 	 => "Â·âˆÂ©",
	"Â·âˆÂ¶" 	 => "Â·âˆÃŸ",
	"Â·âˆÂ§" 	 => "Â·âˆâ€¢",
	"Â·âˆÂ¢" 	 => "Â·âˆÂ£",
	"Â·âˆ " 	 => "Â·âˆÂ°",
	"Â·âˆÃ»" 	 => "Â·âˆÃ¼",
	"Â·âˆÃº" 	 => "Â·âˆÃ¹",
	"Â·âˆÃ¶" 	 => "Â·âˆÃµ",
	"Â·âˆÃ²" 	 => "Â·âˆÃ´",
	"Â·âˆÃ±" 	 => "Â·âˆÃ³",
	"Â·âˆÃ®" 	 => "Â·âˆÃ¯",
	"Â·âˆÃ­" 	  => "Â·âˆÃ¬",
	"Â·âˆÃª" 	 => "Â·âˆÃ«",
	"Â·âˆÃ©" 	 => "Â·âˆÃ¨",
	"Â·âˆÃ¥" 	 => "Â·âˆÃ§",
	"Â·âˆÃ¤" 	 => "Â·âˆÃ£",
	"Â·âˆÃ " 	 => "Â·âˆÃ¢",
	"Â·âˆÃœ" 	 => "Â·âˆÃ¡",
	"Â·âˆÃ‘" 	 => "Â·âˆÃ–",
	"Â·âˆÃ‡" 	 => "Â·âˆÃ‰",
	"Â·âˆÃ„" 	 => "Â·âˆÃ…",
	"â€™Ã±" 	=> "Ã·Ãœ",
	"â€™Ã¯" 	=> "Ã·Ã–",
	"â€™Ã®" 	=> "Ã·Ã‘",
	"â€™Ã¬" 	=> "Ã·Ã‰",
	"â€™Ã­" 	 => "Ã·Ã‡",
	"â€™Ã«" 	=> "Ã·Ã…",
	"â€™Ãª" 	=> "Ã·Ã„",
	"â€™Ã¨" 	=> "â€™Ã¸",
	"â€™Ã©" 	=> "â€™Ã¦",
	"â€™Ã§" 	=> "â€™Î©",
	"â€™Ã¥" 	=> "â€™Âº",
	"â€™Ã£" 	=> "â€™Âª",
	"â€™Ã¤" 	=> "â€™âˆ«",
	"â€™Ã¢" 	=> "â€™Ï€",
	"â€™Ã " 	=> "â€™âˆ",
	"â€™Ã¡" 	=> "â€™âˆ‘",
	"â€™Ãœ" 	=> "â€™âˆ‚",
	"â€™Ã–" 	=> "â€™Âµ",
	"â€™Ã‘" 	=> "â€™Â¥",
	"â€™Ã‰" 	=> "â€™â‰¥",
	"â€™Ã‡" 	=> "â€™â‰¤",
	"â€™Ã…" 	=> "â€™Â±",
	"â€™Ã„" 	=> "â€™âˆž",
	"â€˜Ã¸" 	=> "â€™Ã˜",
	"â€˜Ã¦" 	=> "â€™Ã†",
	"â€˜Î©" 	=> "â€™â‰ ",
	"â€˜Âº" 	=> "â€™Â¨",
	"â€˜Âª" 	=> "â€™Â´",
	"â€˜âˆ«" 	=> "â€™â„¢",
	"â€˜Ï€" 	=> "â€™Â©",
	"â€˜âˆ"	 => "â€™Â®",
	"â€˜âˆ‘" 	=> "â€™ÃŸ",
	"â€˜âˆ‚" 	=> "â€™Â¶",
	"â€˜Âµ" 	=> "â€™â€¢",
	"â€˜Â¥" 	=> "â€™Â§",
	"â€˜â‰¥" 	=> "â€™Â£",
	"â€˜â‰¤" 	=> "â€™Â¢",
	"â€˜Â±" 	=> "â€™Â°",
	"â€˜Ã©" 	=> "â€˜Ã¨",
	"â€˜Ã¥" 	=> "â€˜Ã§",
	"â€˜Ã¤" 	=> "â€˜Ã£",
	"â€˜Ã " 	=> "â€˜Ã¢",
	"â€˜Ãœ" 	=> "â€˜Ã¡",
	"â€˜Ã‘" 	=> "â€˜Ã–",
	"â€˜Ã‡" 	=> "â€˜Ã‰",
	"â€˜Ã„" 	=> "â€˜Ã…",
	"â€âˆ" 	  => "â€Ï€",
	"â€Â¥" 	 => "â€Âµ",
	"â€â‰¤" 	 => "â€â‰¥",
	"â€âˆž" 	 => "â€Â±",
	"â€Ã†" 	 => "â€Ã˜",
	"â€Â¨" 	 => "â€â‰ ",
	"â€â„¢" 	 => "â€Â´",
	"â€Â®" 	 => "â€Â©",
	"â€Â¶" 	 => "â€ÃŸ",
	"â€Â§" 	 => "â€â€¢",
	"â€Â¢" 	 => "â€Â£",
	"â€ " 	 	 => "â€Â°",
	"â€Ã»" 	 => "â€Ã¼",
	"â€Ãº" 	 => "â€Ã¹",
	"â€Ã¶" 	 => "â€Ãµ",
	"â€Ã²" 	 => "â€Ã´",
	"â€Ã±" 	 => "â€Ã³",
	"â€Ã®" 	 => "â€Ã¯",
	"â€Ã­" 	  => "â€Ã¬",
	"â€Ãª" 	 => "â€Ã«",
	"â€Ã§" 	 => "â€Ã©",
	"â€Ã£" 	 => "â€Ã¥",
	"â€Ã¢" 	 => "â€Ã¤",
	"â€Ã¡" 	 => "â€Ã ",
	"â€Ã–" 	 => "â€Ãœ",
	"â€Ã‰" 	 => "â€Ã‘",
	"â€Ã…" 	 => "â€Ã‡",
	"â€œÃ¦" 	=> "â€œÃ¸",
	"â€œÂº" 	=> "â€œÎ©",
	"â€œâˆ«" 	=> "â€œÂª",
	"â€œâˆ" 	 => "â€œÏ€",
	"â€œâˆ‚" 	=> "â€œâˆ‘",
	"â€œÂ¥" 	=> "â€œÂµ",
	"â€œâ‰¤" 	=> "â€œâ‰¥",
	"â€œâˆž" 	=> "â€œÂ±",
	"â€œÃ†" 	=> "â€œÃ˜",
	"â€œÂ¨" 	=> "â€œâ‰ ",
	"â€œâ„¢" 	=> "â€œÂ´",
	"â€œÂ®" 	=> "â€œÂ©",
	"â€œÂ¶" 	=> "â€œÃŸ",
	"â€œÂ§" 	=> "â€œâ€¢",
	"â€œÂ¢" 	=> "â€œÂ£",
	"â€œ " 		=> "â€œÂ°",
	"â€œÃ»" 	=> "â€œÃ¼",
	"â€œÃº" 	=> "â€œÃ¹",
	"â€œÃ¶" 	=> "â€œÃµ",
	"â€œÃ²" 	=> "â€œÃ´",
	"â€œÃ±" 	=> "â€œÃ³",
	"â€œÃ®" 	=> "â€œÃ¯",
	"â€œÃ­"      => "â€œÃ¬",
	"â€œÃª" 	=> "â€œÃ«",
	"â€œÃ©" 	=> "â€œÃ¨",
	"â€œÃ¥" 	=> "â€œÃ§",
	"â€œÃ¤" 	=> "â€œÃ£",
	"â€œÃ„" 	=> "â€œÃ…",
	"â€”Ã¦" 	=> "â€”Ã¸",
	"â€”Âº" 	=> "â€”Î©",
	"â€”âˆ«" 	=> "â€”Âª",
	"â€”âˆ" 	 => "â€”Ï€",
	"â€”âˆ‚"	=> "â€”âˆ‘",
	"â€”Â¥" 	=> "â€”Âµ",
	"â€”â‰¤" 	=> "â€”â‰¥",
	"â€”âˆž" 	=> "â€”Â±",
	"â€”Ã†" 	=> "â€”Ã˜",
	"â€”Â¨" 	=> "â€”â‰ ",
	"â€”â„¢" 	=> "â€”Â´",
	"â€”Â®" 	=> "â€”Â©",
	"â€”Â¶" 	=> "â€”ÃŸ",
	"â€”Â§" 	=> "â€”â€¢",
	"â€”Â¢" 	=> "â€”Â£",
	"â€” " 		=> "â€”Â°",
	"â€“Ã˜" 	=> "â€”Ã¨",
	"â€“Ã†" 	=> "â€”Ã©",
	"â€“â‰ " 	=> "â€”Ã§",
	"â€“Â¨" 	=> "â€”Ã¥",
	"â€“Â´" 	=> "â€”Ã£",
	"â€“â„¢" 	=> "â€”Ã¤",
	"â€“Â©" 	=> "â€”Ã¢",
	"â€“Â®"		=> "â€”Ã ",
	"â€“ÃŸ" 	=> "â€”Ã¡",
	"â€“Â¶" 	=> "â€”Ãœ",
	"â€“â€¢" 	=> "â€”Ã–",
	"â€“Â§" 	=> "â€”Ã‘",
	"â€“Â£" 	=> "â€”Ã‰",
	"â€“Â¢" 	=> "â€”Ã‡",
	"â€“Â°" 	=> "â€”Ã…",
	"â€“ " 		=> "â€”Ã„",
	"â€“Ã¼" 	=> "â€“Ã¸",
	"â€“Ã»" 	=> "â€“Ã¦",
	"â€“Ã¹" 	=> "â€“Î©",
	"â€“Ãº" 	=> "â€“Âº",
	"â€“Ãµ" 	=> "â€“Âª",
	"â€“Ã¶" 	=> "â€“âˆ«",
	"â€“Ã´" 	=> "â€“Ï€",
	"â€“Ã²" 	=> "â€“âˆ",
	"â€“Ã³" 	=> "â€“âˆ‘",
	"â€“Ã±" 	=> "â€“âˆ‚",
	"â€“Ã¯" 	=> "â€“Âµ",
	"â€“Ã®" 	=> "â€“Â¥",
	"â€“Ã¬" 	=> "â€“â‰¥",
	"â€“Ã­" 	 => "â€“â‰¤",
	"â€“Ã«" 	=> "â€“Â±",
	"â€“Ãª" 	=> "â€“âˆž",
	"â€“Ã¨" 	=> "â€”Ã¼",
	"â€“Ã©" 	=> "â€”Ã»",
	"â€“Ã§" 	=> "â€”Ã¹",
	"â€“Ã¥" 	=> "â€”Ãº",
	"â€“Ã£" 	=> "â€”Ãµ",
	"â€“Ã¤" 	=> "â€”Ã¶",
	"â€“Ã¢" 	=> "â€”Ã´",
	"â€“Ã " 	=> "â€”Ã²",
	"â€“Ã¡" 	=> "â€”Ã³",
	"â€“Ãœ" 	=> "â€”Ã±",
	"â€“Ã–" 	=> "â€”Ã¯",
	"â€“Ã‘" 	=> "â€”Ã®",
	"â€“Ã‰" 	=> "â€”Ã¬",
	"â€“Ã‡" 	=> "â€”Ã­",
	"â€“Ã…" 	=> "â€”Ã«",
	"â€“Ã„" 	=> "â€”Ãª",
	"Å“Â¥" 		=> "Å’âˆ",
	"Å“Ã†" 		=> "Å“Ã˜",
	"Å“Â¨" 		=> "Å“â‰ ",
	"Å“â„¢" 	=> "Å“Â´",
	"Å“Â®" 		=> "Å“Â©",
	"Å“Â¶" 		=> "Å“ÃŸ",
	"Å“Â§" 		=> "Å“â€¢",
	"Å“Â¢" 		=> "Å“Â£",
	"Å“ " 		=> "Å“Â°",
	"Å“Ã»" 		=> "Å“Ã¼",
	"Å“Ãº" 		=> "Å“Ã¹",
	"Å“Ã¶" 		=> "Å“Ãµ",
	"Å“Ã²" 		=> "Å“Ã´",
	"Å’Â´" 		=> "Å“Ã£",
	"Å’â„¢" 	=> "Å“Ã¤",
	"Å’Â©" 		=> "Å“Ã¢",
	"Å’Â®" 		=> "Å“Ã ",
	"Å’ÃŸ" 		=> "Å“Ã¡",
	"Å’Â¶" 		=> "Å“Ãœ",
	"Å’â€¢" 	=> "Å“Ã–",
	"Å’Â§" 		=> "Å“Ã‘",
	"Å’Â£" 		=> "Å“Ã‰",
	"Å’Â°" 		=> "Å“Ã…",
	"Å’ " 		=> "Å“Ã„",
	"Å’Ã¼" 		=> "Å’Ã¸",
	"Å’Ã»" 		=> "Å’Ã¦",
	"Å’Ã¹" 		=> "Å’Î©",
	"Å’Ãº" 		=> "Å’Âº",
	"Å’Ãµ" 		=> "Å’Âª",
	"Å’Ã¶" 		=> "Å’âˆ«",
	"Å’Ã´" 		=> "Å’Ï€",
	"Å’Ã²" 		=> "Å’âˆ",
	"Å’Ã³" 		=> "Å’âˆ‘",
	"Å’Ã±" 		=> "Å’âˆ‚",
	"Å’Ã¯" 		=> "Å’Âµ",
	"Å’Ã®" 		=> "Å’Â¥",
	"Å’Ã¬" 		=> "Å’â‰¥",
	"Å’Ã­" 		 => "Å’â‰¤",
	"Å’Ã«"		=> "Å’Â±",
	"Å’Ã¨" 		=> "Å“Ã©",
	"Å’Ã©" 		=> "Å“Ã§",
	"Å’Ã¥" 		=> "Å“Ã¥",
	"Å’Ã¤" 		=> "Å’Ã˜",
	"Å’Ã¢" 		=> "Å’Ã†",
	"Å’Ã " 		=> "Å’â‰ ",
	"Å’Ãœ" 		=> "Å’Â¨",
	"Â»â‰¤" 	=> "Â»â‰¥",
	"Â»âˆž" 	=> "Â»Â±",
	"Â»Ã†" 		=> "Â»Ã˜",
	"Â»Â¨" 		=> "Â»â‰ ",
	"Â»â„¢" 	=> "Â»Â´",
	"Â»Â®" 		=> "Â»Â©",
	"Â»Â¶" 		=> "Â»ÃŸ",
	"Â»Â§" 		=> "Â»â€¢",
	"Â»Â¢" 		=> "Â»Â£",
	"Â» " 		=> "âˆ†Ã»",
	"Â»Ã»" 		=> "Â»Ã¼",
	"Â»Ãº" 		=> "Â»Ã¹",
	"Â»Ã¶" 		=> "Â»Ãµ",
	"Â»Ã²" 		=> "Â»Ã´",
	"Â»Ã±" 		=> "Â»Ã³",
	"Â»Ã®" 		=> "Â»Ã¯",
	"Â»Ã­" 		 => "Â»Ã¬",
	"Â»Ãª" 		=> "Â»Ã«",
	"Â»Ã©" 		=> "Â»Ã¨",
	"Â»Ã¥" 		=> "Â»Ã§",
	"Â»Ã¤" 		=> "Â»Ã£",
	"Â»Ã " 		=> "Â»Ã¢",
	"Â»Ãœ" 		=> "Â»Ã¡",
	"Â»Ã‘" 		=> "Â»Ã–",
	"Â»Ã‡" 		=> "Â»Ã‰",
	"Â»Ã„" 		=> "Â»Ã…",
	"Â«Ã¦" 		=> "Â«Ã¸",
	"Â«Âº" 		=> "Â«Î©",
	"Â«âˆ«" 	=> "Â«Âª",
	"Â«âˆ" 	 => "Â«Ï€",
	"Â«âˆ‘" 	=> "âˆ†Ã¸",
	"Â«âˆ‚" 	=> "âˆ†Ã¯",
	"Â«Â¥" 		=> "Â«Âµ",
	"Â«Â±" 		=> "Â«â‰¥",
	"Â«Ã†" 		=> "Â«Ã˜",
	"Â«Â¨" 		=> "Â«â‰ ",
	"Â«â„¢" 	=> "Â«Â´",
	"Â«Â®" 		=> "Â«Â©",
	"Â«Â¶" 		=> "Â«ÃŸ",
	"Â«Â§" 		=> "Â«â€¢",
	"Â«Â¢" 		=> "Â«Â£",
	"Â« " 		=> "Â«Â°",
	"Â«Ã»" 		=> "Â«Ã¼",
	"Â«Ãµ" 		=> "Â«Ãº",
	"Â«Ã´" 		=> "Â«Ã¶",
	"Â«Ã³" 		=> "Â«Ã²",
	"Â«Ã¯" 		=> "Â«Ã±",
	"Â«Ã¬" 		=> "Â«Ã®",
	"Â«Ã«" 		=> "Â«Ã­",
	"Â«Ã¨" 		=> "Â«Ãª",
	"Â«Ã§" 		=> "Â«Ã©",
	"Â«Ã¤" 		=> "Â«Ã¥",
	"Â«Ã¡" 		=> "Â«Ã¢",
	"Â«Ã‘" 		=> "Â«Ãœ",
	"âˆ†Âº"		=> "âˆ†Î©",
	"âˆ†âˆ" 	 => "âˆ†Ï€",
	"âˆ†âˆ‘" 	=> "Â Ã­",
	"âˆ†Âµ" 	=> "âˆ†âˆ‚",
	"âˆ†â‰¥" 	=> "âˆ†Â¥",
	"âˆ†â‰¤" 	=> "Â Ã£",
	"âˆ†Â±" 	=> "Â Ã¤",
	"âˆ†Ã˜" 	=> "âˆ†âˆž",
	"âˆ†Ã†" 	=> "Â Ã ",
	"âˆ†Â¨" 	=> "âˆ†â‰ ",
	"âˆ†Â©" 	=> "Â Ã‰",
	"âˆ†ÃŸ" 	=> "âˆ†Â®",
	"âˆ†Â¶" 	=> "Â Ã„",
	"âˆ†Â§" 	=> "âˆ†â€¢",
	"âˆ†Â¢" 	=> "âˆ†Â£",
	"âˆ† " 		=> "âˆ†Â°",
	"âˆ†Ã¼" 	=> "â€¦Âµ",
	"âˆ†Ã¹" 	=> "â€¦â‰¤",
	"âˆ†Ãº" 	=> "â€¦Ã˜",
	"âˆ†Ã²" 	=> "âˆ†Ã´",
	"âˆ†Ã³" 	=> "â€¦Â®",
	"âˆ†Ã±" 	=> "â€¦Â©",
	"âˆ†Ã®" 	=> "â€¦Â£",
	"âˆ†Ã¬" 	=> "â€¦ ",
	"âˆ†Ã«" 	=> "âˆ†Ã­",
	"âˆ†Ãª" 	=> "â€¦Ãµ",
	"âˆ†Ã¨" 	=> "â€¦Ã´",
	"âˆ†Ã©" 	=> "Â«Ã¹",
	"âˆ†Ã£" 	=> "âˆ†Ã¥",
	"âˆ†Ã¤" 	=> "â€¦Ã³",
	"âˆ†Ã¢" 	=> "â€¦Ã±",
	"âˆ†Ã¡" 	=> "âˆ†Ã ",
	"âˆ†Ãœ" 	=> "â€¦Ã®",
	"âˆ†Ã‘" 	=> "âˆ†Ã–",
	"âˆ†Ã‡" 	=> "âˆ†Ã‰",
	"âˆ†Ã…" 	=> "â€¦Ã¬",
	"â‰ˆÎ©" 	=> "â‰ˆÃ¦",
	"â‰ˆÂª" 	=> "â‰ˆÂº",
	"â‰ˆÏ€" 	=> "â‰ˆâˆ«",
	"â‰ˆâˆ" 	 => "âˆšÃ¸",
	"â‰ˆâˆ‚"	=> "â‰ˆâˆ‘",
	"â‰ˆÂ¥" 	=> "â‰ˆÂµ",
	"â‰ˆâ‰¤" 	=> "â‰ˆâ‰¥",
	"â‰ˆâˆž" 	=> "â‰ˆÂ±",
	"â‰ˆÃ†" 	=> "â‰ˆÃ˜",
	"â‰ˆÂ¨" 	=> "â‰ˆâ‰ ",
	"â‰ˆâ„¢" 	=> "â‰ˆÂ´",
	"â‰ˆÂ®" 	=> "â‰ˆÂ©",
	"â‰ˆÂ¶" 	=> "â‰ˆÃŸ",
	"â‰ˆÂ§" 	=> "â‰ˆâ€¢",
	"â‰ˆÂ¢" 	=> "â‰ˆÂ£",
	"â‰ˆ " 		=> "â‰ˆÂ°",
	"â‰ˆÃ»" 	=> "â‰ˆÃ¼",
	"â‰ˆÃº" 	=> "â‰ˆÃ¹",
	"â‰ˆÃ¶" 	=> "â‰ˆÃµ",
	"â‰ˆÃ²" 	=> "â‰ˆÃ´",
	"â‰ˆÃ±" 	=> "â‰ˆÃ³",
	"â‰ˆÃ®" 	=> "â‰ˆÃ¯",
	"â‰ˆÃ­" 	 => "â‰ˆÃ¬",
	"â‰ˆÃª" 	=> "â‰ˆÃ«",
	"â‰ˆÃ©" 	=> "â‰ˆÃ¨",
	"â‰ˆÃ¥" 	=> "â‰ˆÃ§",
	"â‰ˆÃ¤" 	=> "â‰ˆÃ£",
	"â‰ˆÃ¡" 	=> "â‰ˆÃ ",
	"â‰ˆÃ–" 	=> "â‰ˆÃœ",
	"â‰ˆÃ‰" 	=> "â‰ˆÃ‘",
	"â‰ˆÃ…" 	=> "â‰ˆÃ‡",
	"Æ’Ã¸" 		=> "â‰ˆÃ„",
	"Æ’Î©" 		=> "Æ’Ã¦",
	"Æ’Âª" 		=> "Æ’Âº",
	"Æ’Ï€"		=> "Æ’âˆ«",
	"Æ’âˆ‚" 	=> "Æ’âˆ‘",
	"Æ’Â¥" 		=> "Æ’Âµ",
	"Æ’â‰¤" 	=> "Æ’â‰¥",
	"Æ’âˆž" 	=> "i",
	"Æ’Ã†" 		=> "Æ’Ã˜",
	"Æ’Â¨" 		=> "Æ’â‰ ",
	"Æ’â„¢" 	=> "Æ’Â´",
	"Æ’Â®" 		=> "Æ’Â©",
	"Æ’Â¶" 		=> "Æ’ÃŸ",
	"Æ’Â§" 		=> "Æ’â€¢",
	"Æ’Â¢" 		=> "Æ’Â£",
	"Æ’ " 		=> "Æ’Â°",
	"Æ’Ã»" 		=> "Æ’Ã¼",
	"Æ’Ãº" 		=> "Æ’Ã¹",
	"Æ’Ã¶" 		=> "Æ’Ãµ",
	"Æ’Ã²" 		=> "Æ’Ã´",
	"Æ’Ã±" 		=> "Æ’Ã³",
	"Æ’Ã®" 		=> "Æ’Ã¯",
	"Æ’Ã­" 		 => "Æ’Ã¬",
	"Æ’Ãª"		=> "Æ’Ã«",
	"Æ’Ã©" 		=> "Æ’Ã¨",
	"Æ’Ã¥"		=> "Æ’Ã§",
	"Æ’Ã¤" 		=> "Æ’Ã£",
	"Æ’Ã "		=> "Æ’Ã¢",
	"Æ’Ãœ" 		=> "Æ’Ã¡",
	"Æ’Ã‘" 		=> "Æ’Ã–",
	"Æ’Ã‡" 		=> "Æ’Ã‰",
	"Æ’Ã„" 		=> "Æ’Ã…",
	"âˆšÃ»" 	=> "âˆšÃ¦",
	"âˆšÃ¹" 	=> "âˆšÎ©",
	"âˆšÃº" 	=> "âˆšÂº",
	"âˆšÃµ" 	=> "âˆšÂª",
	"âˆšÃ¶" 	=> "âˆšâˆ«",
	"âˆšÃ´" 	=> "âˆšÏ€",
	"âˆšÃ²" 	=> "âˆšâˆ",
	"âˆšÃ±" 	=> "âˆšâˆ‚",
	"âˆšÃ¯" 	=> "âˆšÂµ",
	"âˆšÃ®" 	=> "âˆšÂ¥",
	"âˆšÃ¬" 	=> "âˆšâ‰¥",
	"âˆšÃ­" 	 => "âˆšâ‰¤",
	"âˆšÃ«" 	=> "âˆšÂ±",
	"âˆšÃª" 	=> "âˆšâˆž",
	"âˆšÃ¨" 	=> "âˆšÃ˜",
	"âˆšÃ©" 	=> "âˆšÃ†",
	"âˆšÃ§" 	=> "âˆšâ‰ ",
	"âˆšÃ¥" 	=> "âˆšÂ¨",
	"âˆšÃ£" 	=> "âˆšÂ´",
	"âˆšÃ¤" 	=> "âˆšâ„¢",
	"âˆšÃ¢" 	=> "âˆšÂ©",
	"âˆšÃ " 	=> "âˆšÂ®",
	"âˆšÃ¡" 	=> "âˆšÃŸ",
	"âˆšÃœ" 	=> "âˆšÂ¶",
	"âˆšÃ–" 	=> "âˆšâ€¢",
	"âˆšÃ‘" 	=> "âˆšÂ§",
	"âˆšÃ‰" 	=> "âˆšÂ£",
	"âˆšÃ‡" 	=> "âˆšÂ¢",
	"âˆšÃ…" 	=> "âˆšÂ°",
	"âˆšÃ„" 	=> "âˆš ",
	"Z" 		=> "z",
	"Y" 		=> "y",
	"X" 		=> "x",
	"W" 		=> "w",
	"V" 		=> "v",
	"U" 		=> "u",
	"T" 		=> "t",
	"S" 		=> "s",
	"R" 		=> "r",
	"Q" 		=> "q",
	"P" 		=> "p",
	"O" 		=> "o",
	"N" 		=> "n",
	"M" 		=> "m",
	"L" 		=> "l",
	"K" 		=> "k",
	"J" 		=> "j",
	"I" 		=> "i",
	"H" 		=> "h",
	"G" 		=> "g",
	"F" 		=> "f",
	"E" 		=> "e",
	"D" 		=> "d",
	"C" 		=> "c",
	"B" 		=> "b",
	"A" 		=> "a",
);
$UTF8_TABLES['strtoupper'] = array(
	"Ã”Î©Ã¶" 	=> "Ã”Âºâˆ«",
	"Ã”Î©Ã´" 	=> "Ã”ÂºÏ€",
	"Ã”Î©Ã²" 	=> "Ã”Âºâˆ",
	"Ã”Î©Ã³" 	=> "Ã”Âºâˆ‘",
	"Ã”Î©Ã±" 	=> "Ã”Âºâˆ‚",
	"Ã”Î©Ã¯" 	=> "Ã”ÂºÂµ",
	"Ã”Î©Ã®" 	=> "Ã”ÂºÂ¥",
	"Ã”Î©Ã¬" 	=> "Ã”Âºâ‰¥",
	"Ã”Î©Ã­" 	 => "Ã”Âºâ‰¤",
	"Ã”Î©Ã«" 	=> "Ã”ÂºÂ±",
	"Ã”Î©Ãª" 	=> "Ã”Âºâˆž",
	"Ã”Î©Ã¨" 	=> "Ã”ÂºÃ˜",
	"Ã”Î©Ã©" 	=> "Ã”ÂºÃ†",
	"Ã”Î©Ã§" 	=> "Ã”Âºâ‰ ",
	"Ã”Î©Ã¥" 	=> "Ã”ÂºÂ¨",
	"Ã”Î©Ã£" 	=> "Ã”ÂºÂ´",
	"Ã”Î©Ã¤" 	=> "Ã”Âºâ„¢",
	"Ã”Î©Ã¢" 	=> "Ã”ÂºÂ©",
	"Ã”Î©Ã " 	=> "Ã”ÂºÂ®",
	"Ã”Î©Ã¡" 	=> "Ã”ÂºÃŸ",
	"Ã”Î©Ãœ" 	=> "Ã”ÂºÂ¶",
	"Ã”Î©Ã–" 	=> "Ã”Âºâ€¢",
	"Ã”Î©Ã‘" 	=> "Ã”ÂºÂ§",
	"Ã”Î©Ã‰" 	=> "Ã”ÂºÂ£",
	"Ã”Î©Ã‡" 	=> "Ã”ÂºÂ¢",
	"Ã”Î©Ã…" 	=> "Ã”ÂºÂ°",
	"Â·Ã¸â‰¥" 	=> "Â·Ã¸Âº",
	"Â·Ã¸â€¢"	=> "Â·Ã¸Â¨",
	"Â·Ã¸Â°" 	=> "Â·Ã¸Â©",
	"Â·Ã¸ " 	=> "Â·Ã¸Â®",
	"Â·Ã¸Ã«" 	=> "Â·Ã¸Ã´",
	"Â·Ã¸Ãª" 	=> "Â·Ã¸Ã²",
	"Â·Ã¸Ã‰" 	=> "Â·Ã¸Ã¥",
	"Â·Ã¦Ã¦" 	=> "Å’Ã´",
	"Â·Ã¦â‰¥" 	=> "Â·Ã¦Âº",
	"Â·Ã¦Â±"	=> "Â·Ã¦Ï€",
	"Â·Ã¦âˆž" 	=> "Â·Ã¦âˆ",
	"Â·Ã¦ÃŸ" 	=> "Â·Ã¦Ã˜",
	"Â·Ã¦Â¶" 	=> "Â·Ã¦Ã†",
	"Â·Ã¦â€¢"	=> "Â·Ã¦â‰ ",
	"Â·Ã¦Â§" 	=> "Â·Ã¦Â¨",
	"Â·Ã¦Â£" 	=> "Â·Ã¦Â´",
	"Â·Ã¦Â¢" 	=> "Â·Ã¦â„¢",
	"Â·Ã¦Â°" 	=> "Â·Ã¦Â©",
	"Â·Ã¦ " 	=> "Â·Ã¦Â®",
	"Â·Ã¦Ã³" 	=> "Â·Ã¦Ã¼",
	"Â·Ã¦Ã±" 	=> "Â·Ã¦Ã»",
	"Â·Ã¦Ã¯" 	=> "Â·Ã¦Ã¹",
	"Â·Ã¦Ã®" 	=> "Â·Ã¦Ãº",
	"Â·Ã¦Ã¬" 	=> "Â·Ã¦Ãµ",
	"Â·Ã¦Ã­" 	 => "Â·Ã¦Ã¶",
	"Â·Ã¦Ã«" 	=> "Â·Ã¦Ã´",
	"Â·Ã¦Ãª" 	=> "Â·Ã¦Ã²",
	"Â·Ã¦Ã¡" 	=> "Â·Ã¦Ã¨",
	"Â·Ã¦Ãœ" 	=> "Â·Ã¦Ã©",
	"Â·Ã¦Ã–" 	=> "Â·Ã¦Ã§",
	"Â·Ã¦Ã‘" 	=> "Â·Ã¦Ã¥",
	"Â·Ã¦Ã‰" 	=> "Â·Ã¦Ã£",
	"Â·Ã¦Ã‡" 	=> "Â·Ã¦Ã¤",
	"Â·Ã¦Ã…" 	=> "Â·Ã¦Ã¢",
	"Â·Ã¦Ã„" 	=> "Â·Ã¦Ã ",
	"Â·Î©Î©" 	=> "Â·Ã¸Âª",
	"Â·Î©Âº" 	=> "Â·Ã¸âˆ«",
	"Â·Î©Âª" 	=> "Â·Ã¸Â´",
	"Â·Î©âˆ«" 	=> "Â·Ã¸â„¢",
	"Â·Î©Ï€" 	=> "Â·Ã¸Ï€",
	"Â·Î©âˆ" 	 => "Â·Ã¸âˆ",
	"Â·Î©âˆ‘" 	=> "Â·Ã¸Ãµ",
	"Â·Î©âˆ‚" 	=> "Â·Ã¸Ã¶",
	"Â·Î©Âµ" 	=> "Â·Ã¸Ã£",
	"Â·Î©Â¥" 	=> "Â·Ã¸Ã¤",
	"Â·Î©â‰¥"	=> "Â·Ã¸Ã¢",
	"Â·Î©â‰¤" 	=> "Â·Ã¸Ã ",
	"Â·Î©Â±" 	=> "Â·Ã¦Âª",
	"Â·Î©âˆž" 	=> "Â·Ã¦âˆ«",
	"Â·Î©ÃŸ" 	=> "Â·Î©Ã˜",
	"Â·Î©Â¶" 	=> "Â·Î©Ã†",
	"Â·Î©â€¢" 	=> "Â·Î©â‰ ",
	"Â·Î©Â§" 	=> "Â·Î©Â¨",
	"Â·Î©Â£" 	=> "Â·Î©Â´",
	"Â·Î©Â¢" 	=> "Â·Î©â„¢",
	"Â·Î©Â°" 	=> "Â·Î©Â©",
	"Â·Î© " 	=> "Â·Î©Â®",
	"Â·Î©Ã³" 	=> "Â·Î©Ã¼",
	"Â·Î©Ã¯" 	=> "Â·Î©Ã¹",
	"Â·Î©Ã¬" 	=> "Â·Î©Ãµ",
	"Â·Î©Ã«" 	=> "Â·Î©Ã´",
	"Â·Î©Ã–" 	=> "Â·Î©Ã§",
	"Â·Î©Ã‘" 	=> "Â·Î©Ã¥",
	"Â·Î©Ã‰" 	=> "Â·Î©Ã£",
	"Â·Î©Ã‡" 	=> "Â·Î©Ã¤",
	"Â·Î©Ã…" 	=> "Â·Î©Ã¢",
	"Â·Î©Ã„" 	=> "Â·Î©Ã ",
	"Â·Âºâˆ‘" 	=> "Â·ÂºÃ¸",
	"Â·Âºâˆ‚" 	=> "Â·ÂºÃ¦",
	"Â·ÂºÂµ" 	=> "Â·ÂºÎ©",
	"Â·ÂºÂ¥" 	=> "Â·ÂºÂº",
	"Â·Âºâ‰¥"	=> "Â·ÂºÂª",
	"Â·Âºâ‰¤" 	=> "Â·Âºâˆ«",
	"Â·ÂºÂ±" 	=> "Â·ÂºÏ€",
	"Â·Âºâˆž" 	=> "Â·Âºâˆ",
	"Â·ÂºÃŸ" 	=> "Â·ÂºÃ˜",
	"Â·ÂºÂ¶" 	=> "Â·ÂºÃ†",
	"Â·Âºâ€¢" 	=> "Â·Âºâ‰ ",
	"Â·ÂºÂ§" 	=> "Â·ÂºÂ¨",
	"Â·ÂºÂ£" 	=> "Â·ÂºÂ´",
	"Â·ÂºÂ¢" 	=> "Â·Âºâ„¢",
	"Â·ÂºÂ°" 	=> "Â·ÂºÂ©",
	"Â·Âº " 	=> "Â·ÂºÂ®",
	"Â·ÂºÃ¯" 	=> "Â·ÂºÃ¹",
	"Â·ÂºÃ®" 	=> "Â·ÂºÃº",
	"Â·ÂºÃ¬" 	=> "Â·ÂºÃµ",
	"Â·ÂºÃ­" 	 => "Â·ÂºÃ¶",
	"Â·ÂºÃ«" 	=> "Â·ÂºÃ´",
	"Â·ÂºÃª" 	=> "Â·ÂºÃ²",
	"Â·ÂºÃ¡" 	=> "Â·ÂºÃ¨",
	"Â·ÂºÃœ" 	=> "Â·ÂºÃ©",
	"Â·ÂºÃ–" 	=> "Â·ÂºÃ§",
	"Â·ÂºÃ‘" 	=> "Â·ÂºÃ¥",
	"Â·ÂºÃ‰" 	=> "Â·ÂºÃ£",
	"Â·ÂºÃ‡" 	=> "Â·ÂºÃ¤",
	"Â·ÂºÃ…" 	=> "Â·ÂºÃ¢",
	"Â·ÂºÃ„" 	=> "Â·ÂºÃ ",
	"Â·ÂªÏ€" 	=> "Â·Âªâˆ",
	"Â·Âªâˆ‘" 	=> "Â·Âªâˆ‚",
	"Â·ÂªÂµ" 	=> "Â·ÂªÂ¥",
	"Â·Âªâ‰¥" 	=> "Â·Âªâ‰¤",
	"Â·ÂªÂ±" 	=> "Â·Âªâˆž",
	"Â·ÂªÃ˜" 	=> "Â·ÂªÃ†",
	"Â·Âªâ‰ " 	=> "Â·ÂªÂ¨",
	"Â·ÂªÂ´" 	=> "Â·Âªâ„¢",
	"Â·ÂªÂ©" 	=> "Â·ÂªÂ®",
	"Â·ÂªÃŸ" 	=> "Â·ÂªÂ¶",
	"Â·Âªâ€¢" 	=> "Â·ÂªÂ§",
	"Â·ÂªÂ£" 	=> "Â·ÂªÂ¢",
	"Â·ÂªÂ°" 	=> "Â·Âª ",
	"Â·ÂªÃ¼" 	=> "Â·ÂªÃ»",
	"Â·ÂªÃ¹" 	=> "Â·ÂªÃº",
	"Â·ÂªÃµ" 	=> "Â·ÂªÃ¶",
	"Â·ÂªÃ´" 	=> "Â·ÂªÃ²",
	"Â·ÂªÃ³" 	=> "Â·ÂªÃ±",
	"Â·ÂªÃ¯" 	=> "Â·ÂªÃ®",
	"Â·ÂªÃ¬" 	=> "Â·ÂªÃ­",
	"Â·ÂªÃ«" 	=> "Â·ÂªÃª",
	"Â·ÂªÃ¨" 	=> "Â·ÂªÃ©",
	"Â·ÂªÃ§" 	=> "Â·ÂªÃ¥",
	"Â·ÂªÃ£" 	=> "Â·ÂªÃ¤",
	"Â·ÂªÃ¢" 	=> "Â·ÂªÃ ",
	"Â·ÂªÃ¡" 	=> "Â·ÂªÃœ",
	"Â·ÂªÃ–" 	=> "Â·ÂªÃ‘",
	"Â·ÂªÃ‰" 	=> "Â·ÂªÃ‡",
	"Â·ÂªÃ…" 	=> "Â·ÂªÃ„",
	"Â·âˆ«Ã¸" 	=> "Â·âˆ«Ã¦",
	"Â·âˆ«Î©" 	=> "Â·âˆ«Âº",
	"Â·âˆ«Âª" 	=> "Â·âˆ«âˆ«",
	"Â·âˆ«Ï€" 	=> "Â·âˆ«âˆ",
	"Â·âˆ«âˆ‘"	=> "Â·âˆ«âˆ‚",
	"Â·âˆ«Âµ" 	=> "Â·âˆ«Â¥",
	"Â·âˆ«â‰¥" 	=> "Â·âˆ«â‰¤",
	"Â·âˆ«Â±" 	=> "Â·âˆ«âˆž",
	"Â·âˆ«Ã˜" 	=> "Â·âˆ«Ã†",
	"Â·âˆ«â‰ " 	=> "Â·âˆ«Â¨",
	"Â·âˆ«Â´" 	=> "Â·âˆ«â„¢",
	"Â·âˆ«Â©" 	=> "Â·âˆ«Â®",
	"Â·âˆ«ÃŸ" 	=> "Â·âˆ«Â¶",
	"Â·âˆ«â€¢" 	=> "Â·âˆ«Â§",
	"Â·âˆ«Â£" 	=> "Â·âˆ«Â¢",
	"Â·âˆ«Â°" 	=> "Â·âˆ« ",
	"Â·âˆ«Ãµ" 	=> "Â·Ï€ ",
	"Â·âˆ«Ã¯" 	=> "Â·âˆ«Ã®",
	"Â·âˆ«Ã¬" 	=> "Â·âˆ«Ã­",
	"Â·âˆ«Ã«" 	=> "Â·âˆ«Ãª",
	"Â·âˆ«Ã¨" 	=> "Â·âˆ«Ã©",
	"Â·âˆ«Ã§" 	=> "Â·âˆ«Ã¥",
	"Â·âˆ«Ã£" 	=> "Â·âˆ«Ã¤",
	"Â·âˆ«Ã¢" 	=> "Â·âˆ«Ã ",
	"Â·âˆ«Ã¡" 	=> "Â·âˆ«Ãœ",
	"Â·âˆ«Ã–" 	=> "Â·âˆ«Ã‘",
	"Â·âˆ«Ã‰" 	=> "Â·âˆ«Ã‡",
	"Â·âˆ«Ã…" 	=> "Â·âˆ«Ã„",
	"Â·Ï€Ã¸" 	=> "Â·Ï€Ã¦",
	"Â·Ï€Î©" 	=> "Â·Ï€Âº",
	"Â·Ï€Âª" 	=> "Â·Ï€âˆ«",
	"Â·Ï€Ï€" 	=> "Â·Ï€âˆ",
	"Â·Ï€âˆ‘" 	=> "Â·Ï€âˆ‚",
	"Â·Ï€Âµ" 	=> "Â·Ï€Â¥",
	"Â·Ï€â‰¥" 	=> "Â·Ï€â‰¤",
	"Â·Ï€Â±" 	=> "Â·Ï€âˆž",
	"Â·Ï€Ã˜" 	=> "Â·Ï€Ã†",
	"Â·Ï€â‰ " 	=> "Â·Ï€Â¨",
	"Â·Ï€Â´" 	=> "Â·Ï€â„¢",
	"Â·Ï€Â©" 	=> "Â·Ï€Â®",
	"Â·Ï€ÃŸ" 	=> "Â·Ï€Â¶",
	"Â·Ï€â€¢" 	=> "Â·Ï€Â§",
	"Â·Ï€Â£" 	=> "Â·Ï€Â¢",
	"Â·Ï€Â°" 	=> "Â·Ï€ ",
	"Â·Ï€Ã¼" 	=> "Â·Ï€Ã»",
	"Â·Ï€Ã¹" 	=> "Â·Ï€Ãº",
	"Â·Ï€Ãµ" 	=> "Â·Ï€Ã¶",
	"Â·Ï€Ã´" 	=> "Â·Ï€Ã²",
	"Â·Ï€Ã³" 	=> "Â·Ï€Ã±",
	"Â·Ï€Ã¯" 	=> "Â·Ï€Ã®",
	"Â·Ï€Ã¬" 	=> "Â·Ï€Ã­",
	"Â·Ï€Ã«" 	=> "Â·Ï€Ãª",
	"Â·Ï€Ã¨" 	=> "Â·Ï€Ã©",
	"Â·Ï€Ã§" 	=> "Â·Ï€Ã¥",
	"Â·Ï€Ã£" 	=> "Â·Ï€Ã¤",
	"Â·Ï€Ã¢" 	=> "Â·Ï€Ã ",
	"Â·Ï€Ã¡" 	=> "Â·Ï€Ãœ",
	"Â·Ï€Ã–" 	=> "Â·Ï€Ã‘",
	"Â·Ï€Ã‰" 	=> "Â·Ï€Ã‡",
	"Â·Ï€Ã…" 	=> "Â·Ï€Ã„",
	"Â·âˆÃ¸" 	 => "Â·âˆÃ¦",
	"Â·âˆÎ©" 	 => "Â·âˆÂº",
	"Â·âˆÂª" 	 => "Â·âˆâˆ«",
	"Â·âˆÏ€" 	 => "Â·âˆâˆ",
	"Â·âˆâˆ‘" 	 => "Â·âˆâˆ‚",
	"Â·âˆÂµ"  	 => "Â·âˆÂ¥",
	"Â·âˆâ‰¥" 	 => "Â·âˆâ‰¤",
	"Â·âˆÂ±"  	 => "Â·âˆâˆž",
	"Â·âˆÃ˜" 	 => "Â·âˆÃ†",
	"Â·âˆâ‰ " 	 => "Â·âˆÂ¨",
	"Â·âˆÂ´" 	 => "Â·âˆâ„¢",
	"Â·âˆÂ©" 	 => "Â·âˆÂ®",
	"Â·âˆÃŸ" 	 => "Â·âˆÂ¶",
	"Â·âˆâ€¢" 	 => "Â·âˆÂ§",
	"Â·âˆÂ£" 	 => "Â·âˆÂ¢",
	"Â·âˆÂ°" 	 => "Â·âˆ ",
	"Â·âˆÃ¼" 	 => "Â·âˆÃ»",
	"Â·âˆÃ¹" 	 => "Â·âˆÃº",
	"Â·âˆÃµ" 	 => "Â·âˆÃ¶",
	"Â·âˆÃ´" 	 => "Â·âˆÃ²",
	"Â·âˆÃ³" 	 => "Â·âˆÃ±",
	"Â·âˆÃ¯" 	 => "Â·âˆÃ®",
	"Â·âˆÃ¬" 	 => "Â·âˆÃ­",
	"Â·âˆÃ«" 	 => "Â·âˆÃª",
	"Â·âˆÃ¨" 	 => "Â·âˆÃ©",
	"Â·âˆÃ§" 	 => "Â·âˆÃ¥",
	"Â·âˆÃ£" 	 => "Â·âˆÃ¤",
	"Â·âˆÃ¢" 	 => "Â·âˆÃ ",
	"Â·âˆÃ¡" 	 => "Â·âˆÃœ",
	"Â·âˆÃ–" 	 => "Â·âˆÃ‘",
	"Â·âˆÃ‰" 	 => "Â·âˆÃ‡",
	"Â·âˆÃ…" 	 => "Â·âˆÃ„",
	"Ã·Ãœ" 	 	=> "â€™Ã±",
	"Ã·Ã–" 		=> "â€™Ã¯",
	"Ã·Ã‘" 		=> "â€™Ã®",
	"Ã·Ã‰" 		=> "â€™Ã¬",
	"Ã·Ã‡" 		=> "â€™Ã­",
	"Ã·Ã…" 		=> "â€™Ã«",
	"Ã·Ã„" 		=> "â€™Ãª",
	"â€™Ã¸"		=> "â€™Ã¨",
	"â€™Ã¦" 	=> "â€™Ã©",
	"â€™Î©" 	=> "â€™Ã§",
	"â€™Âº" 	=> "â€™Ã¥",
	"â€™Âª" 	=> "â€™Ã£",
	"â€™âˆ«"	=> "â€™Ã¤",
	"â€™Ï€" 	=> "â€™Ã¢",
	"â€™âˆ" 	 => "â€™Ã ",
	"â€™âˆ‘" 	=> "â€™Ã¡",
	"â€™âˆ‚" 	=> "â€™Ãœ",
	"â€™Âµ" 	=> "â€™Ã–",
	"â€™Â¥" 	=> "â€™Ã‘",
	"â€™â‰¥" 	=> "â€™Ã‰",
	"â€™â‰¤" 	=> "â€™Ã‡",
	"â€™Â±" 	=> "â€™Ã…",
	"â€™âˆž" 	=> "â€™Ã„",
	"â€™Ã˜" 	=> "â€˜Ã¸",
	"â€™Ã†" 	=> "â€˜Ã¦",
	"â€™â‰ " 	=> "â€˜Î©",
	"â€™Â¨" 	=> "â€˜Âº",
	"â€™Â´" 	=> "â€˜Âª",
	"â€™â„¢" 	=> "â€˜âˆ«",
	"â€™Â©" 	=> "â€˜Ï€",
	"â€™Â®" 	=> "â€˜âˆ",
	"â€™ÃŸ" 	=> "â€˜âˆ‘",
	"â€™Â¶" 	=> "â€˜âˆ‚",
	"â€™â€¢" 	=> "â€˜Âµ",
	"â€™Â§" 	=> "â€˜Â¥",
	"â€™Â£" 	=> "â€˜â‰¥",
	"â€™Â¢" 	=> "â€˜â‰¤",
	"â€™Â°" 	=> "â€˜Â±",
	"â€˜Ã¨" 	=> "â€˜Ã©",
	"â€˜Ã§" 	=> "â€˜Ã¥",
	"â€˜Ã£" 	=> "â€˜Ã¤",
	"â€˜Ã¢" 	=> "â€˜Ã ",
	"â€˜Ã¡" 	=> "â€˜Ãœ",
	"â€˜Ã–" 	=> "â€˜Ã‘",
	"â€˜Ã‰" 	=> "â€˜Ã‡",
	"â€˜Ã…" 	=> "â€˜Ã„",
	"â€Ï€" 	 => "â€âˆ",
	"â€Âµ" 	 => "â€Â¥",
	"â€â‰¥" 	 => "â€â‰¤",
	"â€Â±" 	 => "â€âˆž",
	"â€Ã˜" 	 => "â€Ã†",
	"â€â‰ "	 => "â€Â¨",
	"â€Â´" 	 => "â€â„¢",
	"â€Â©" 	 => "â€Â®",
	"â€ÃŸ" 	 => "â€Â¶",
	"â€â€¢"	 => "â€Â§",
	"â€Â£" 	 => "â€Â¢",
	"â€Â°" 	 => "â€ ",
	"â€Ã¼" 	 => "â€Ã»",
	"â€Ã¹" 	 => "â€Ãº",
	"â€Ãµ" 	 => "â€Ã¶",
	"â€Ã´" 	 => "â€Ã²",
	"â€Ã³" 	 => "â€Ã±",
	"â€Ã¯" 	 => "â€Ã®",
	"â€Ã¬" 	 => "â€Ã­",
	"â€Ã«" 	 => "â€Ãª",
	"â€Ã©" 	 => "â€Ã§",
	"â€Ã¥" 	 => "â€Ã£",
	"â€Ã¤" 	 => "â€Ã¢",
	"â€Ã " 	 => "â€Ã¡",
	"â€Ãœ" 	 => "â€Ã–",
	"â€Ã‘" 	 => "â€Ã‰",
	"â€Ã‡" 	 => "â€Ã…",
	"â€œÃ¸" 	=> "â€œÃ¦",
	"â€œÎ©" 	=> "â€œÂº",
	"â€œÂª" 	=> "â€œâˆ«",
	"â€œÏ€" 	=> "â€œâˆ",
	"â€œâˆ‘"	=> "â€œâˆ‚",
	"â€œÂµ" 	=> "â€œÂ¥",
	"â€œâ‰¥" 	=> "â€œâ‰¤",
	"â€œÂ±" 	=> "â€œâˆž",
	"â€œÃ˜" 	=> "â€œÃ†",
	"â€œâ‰ " 	=> "â€œÂ¨",
	"â€œÂ´" 	=> "â€œâ„¢",
	"â€œÂ©" 	=> "â€œÂ®",
	"â€œÃŸ" 	=> "â€œÂ¶",
	"â€œâ€¢" 	=> "â€œÂ§",
	"â€œÂ£" 	=> "â€œÂ¢",
	"â€œÂ°" 	=> "â€œ ",
	"â€œÃ¼" 	=> "â€œÃ»",
	"â€œÃ¹" 	=> "â€œÃº",
	"â€œÃµ" 	=> "â€œÃ¶",
	"â€œÃ´" 	=> "â€œÃ²",
	"â€œÃ³" 	=> "â€œÃ±",
	"â€œÃ¯" 	=> "â€œÃ®",
	"â€œÃ¬" 	=> "â€œÃ­",
	"â€œÃ«" 	=> "â€œÃª",
	"â€œÃ¨" 	=> "â€œÃ©",
	"â€œÃ§" 	=> "â€œÃ¥",
	"â€œÃ£" 	=> "â€œÃ¤",
	"â€œÃ…" 	=> "â€œÃ„",
	"â€”Ã¸" 	=> "â€”Ã¦",
	"â€”Î©" 	=> "â€”Âº",
	"â€”Âª" 	=> "â€”âˆ«",
	"â€”Ï€" 	=> "â€”âˆ",
	"â€”âˆ‘" 	=> "â€”âˆ‚",
	"â€”Âµ" 	=> "â€”Â¥",
	"â€”â‰¥" 	=> "â€”â‰¤",
	"â€”Â±" 	=> "â€”âˆž",
	"â€”Ã˜" 	=> "â€”Ã†",
	"â€”â‰ " 	=> "â€”Â¨",
	"â€”Â´" 	=> "â€”â„¢",
	"â€”Â©" 	=> "â€”Â®",
	"â€”ÃŸ" 	=> "â€”Â¶",
	"â€”â€¢" 	=> "â€”Â§",
	"â€”Â£" 	=> "â€”Â¢",
	"â€”Â°" 	=> "â€” ",
	"â€”Ã¼" 	=> "â€“Ã¨",
	"â€”Ã»" 	=> "â€“Ã©",
	"â€”Ã¹" 	=> "â€“Ã§",
	"â€”Ãº" 	=> "â€“Ã¥",
	"â€”Ãµ" 	=> "â€“Ã£",
	"â€”Ã¶" 	=> "â€“Ã¤",
	"â€”Ã´" 	=> "â€“Ã¢",
	"â€”Ã²" 	=> "â€“Ã ",
	"â€”Ã³" 	=> "â€“Ã¡",
	"â€”Ã±" 	=> "â€“Ãœ",
	"â€”Ã¯" 	=> "â€“Ã–",
	"â€”Ã®" 	=> "â€“Ã‘",
	"â€”Ã¬" 	=> "â€“Ã‰",
	"â€”Ã­" 	 => "â€“Ã‡",
	"â€”Ã«" 	=> "â€“Ã…",
	"â€”Ãª" 	=> "â€“Ã„",
	"â€”Ã¨" 	=> "â€“Ã˜",
	"â€”Ã©" 	=> "â€“Ã†",
	"â€”Ã§" 	=> "â€“â‰ ",
	"â€”Ã¥" 	=> "â€“Â¨",
	"â€”Ã£" 	=> "â€“Â´",
	"â€”Ã¤" 	=> "â€“â„¢",
	"â€”Ã¢" 	=> "â€“Â©",
	"â€”Ã " 	=> "â€“Â®",
	"â€”Ã¡" 	=> "â€“ÃŸ",
	"â€”Ãœ" 	=> "â€“Â¶",
	"â€”Ã–" 	=> "â€“â€¢",
	"â€”Ã‘" 	=> "â€“Â§",
	"â€”Ã‰" 	=> "â€“Â£",
	"â€”Ã‡" 	=> "â€“Â¢",
	"â€”Ã…" 	=> "â€“Â°",
	"â€”Ã„" 	=> "â€“ ",
	"â€“Ã¸" 	=> "â€“Ã¼",
	"â€“Ã¦" 	=> "â€“Ã»",
	"â€“Î©" 	=> "â€“Ã¹",
	"â€“Âº" 	=> "â€“Ãº",
	"â€“Âª" 	=> "â€“Ãµ",
	"â€“âˆ«" 	=> "â€“Ã¶",
	"â€“Ï€" 	=> "â€“Ã´",
	"â€“âˆ" 	 => "â€“Ã²",
	"â€“âˆ‘" 	=> "â€“Ã³",
	"â€“âˆ‚" 	=> "â€“Ã±",
	"â€“Âµ" 	=> "â€“Ã¯",
	"â€“Â¥" 	=> "â€“Ã®",
	"â€“â‰¥" 	=> "â€“Ã¬",
	"â€“â‰¤" 	=> "â€“Ã­",
	"â€“Â±" 	=> "â€“Ã«",
	"â€“âˆž" 	=> "â€“Ãª",
	"Å“Âµ" 		=> "Å’Ã¯",
	"Å“â‰¤" 	=> "Å’Â£",
	"Å“Â±" 		=> "Å’Â°",
	"Å“âˆž" 	=> "Å’Ã¶",
	"Å“Ã˜" 		=> "Å“Ã†",
	"Å“â‰ " 	=> "Å“Â¨",
	"Å“Â´" 		=> "Å“â„¢",
	"Å“Â©" 		=> "Å“Â®",
	"Å“ÃŸ" 		=> "Å“Â¶",
	"Å“â€¢"		=> "Å“Â§",
	"Å“Â£" 		=> "Å“Â¢",
	"Å“Â°" 		=> "Å“ ",
	"Å“Ã¼" 		=> "Å“Ã»",
	"Å“Ã¹" 		=> "Å“Ãº",
	"Å“Ãµ" 		=> "Å“Ã¶",
	"Å“Ã´" 		=> "Å“Ã²",
	"Å“Ã±" 		=> "Å’ ",
	"Å“Ã¯" 		=> "Å’Â¶",
	"Å“Ã«" 		=> "Å’Ã²",
	"Å“Ãª" 		=> "Å’Ã­",
	"Å“Ã©" 		=> "Å’Ã¨",
	"Å“Ã§" 		=> "Å’Ã©",
	"Å“Ã¥" 		=> "Å’Ã¥",
	"Å“Ã£" 		=> "Å’Â´",
	"Å“Ã¤" 		=> "Å’â„¢",
	"Å“Ã¢" 		=> "Å’Â©",
	"Å“Ã " 		=> "Å’Â®",
	"Å“Ã¡" 		=> "Å’ÃŸ",
	"Å“Ãœ" 		=> "Å’Â¶",
	"Å“Ã–" 		=> "Å’â€¢",
	"Å“Ã‘" 		=> "Å’Â§",
	"Å“Ã‰" 		=> "Å’Â£",
	"Å“Ã‡" 		=> "Å’Â£",
	"Å“Ã…" 		=> "Å’Â°",
	"Å“Ã„" 		=> "Å’ ",
	"Å’Ã¸" 		=> "Å’Ã¼",
	"Å’Ã¦" 		=> "Å’Ã»",
	"Å’Î©" 		=> "Å’Ã¹",
	"Å’Âº" 		=> "Å’Ãº",
	"Å’Âª" 		=> "Å’Ãµ",
	"Å’âˆ«"		=> "Å’Ã¶",
	"Å’Ï€" 		=> "Å’Ã´",
	"Å’âˆ" 	 => "Å’Ã²",
	"Å’âˆ‘" 	=> "Å’Ã³",
	"Å’âˆ‚" 	=> "Å’Ã±",
	"Å’Âµ" 		=> "Å’Ã¯",
	"Å’Â¥" 		=> "Å’Ã®",
	"Å’â‰¥" 	=> "Å’Ã¬",
	"Å’â‰¤" 	=> "Å’Ã­",
	"Å’Â±" 		=> "Å’Ã«",
	"Å’Ã˜" 		=> "Å’Ã¤",
	"Å’Ã†" 		=> "Å’Ã¢",
	"Å’â‰ " 	=> "Å’Ã ",
	"Å’Â¨" 		=> "Å’Ãœ",
	"Â Ã­" 		 => "âˆ†âˆ‘",
	"Â Ã£" 		=> "âˆ†â‰¤",
	"Â Ã¤" 		=> "âˆ†Â±",
	"Â Ã " 		=> "âˆ†Ã†",
	"Â Ã‰" 		=> "âˆ†Â©",
	"Â Ã„" 		=> "âˆ†Â¶",
	"â€¦Âµ"		=> "âˆ†Ã¼",
	"â€¦â‰¤" 	=> "âˆ†Ã¹",
	"â€¦Ã˜" 	=> "âˆ†Ãº",
	"â€¦Â©" 	=> "âˆ†Ã±",
	"â€¦Â®" 	=> "âˆ†Ã³",
	"â€¦Â£" 	=> "âˆ†Ã®",
	"â€¦ " 		=> "âˆ†Ã¬",
	"â€¦Ãµ" 	=> "âˆ†Ãª",
	"â€¦Ã´" 	=> "âˆ†Ã¨",
	"â€¦Ã³" 	=> "âˆ†Ã¤",
	"â€¦Ã±" 	=> "âˆ†Ã¢",
	"â€¦Ã®" 	=> "âˆ†Ãœ",
	"â€¦Ã¬" 	=> "âˆ†Ã…",
	"Â»â‰¥" 	=> "Â»â‰¤",
	"Â»Â±" 		=> "Â»âˆž",
	"Â»Ã˜" 		=> "Â»Ã†",
	"Â»â‰ " 	=> "Â»Â¨",
	"Â»Â´" 		=> "Â»â„¢",
	"Â»Â©" 		=> "Â»Â®",
	"Â»ÃŸ" 		=> "Â»Â¶",
	"Â»â€¢" 	=> "Â»Â§",
	"Â»Â£" 		=> "Â»Â¢",
	"Â»Ã¼" 		=> "Â»Ã»",
	"Â»Ã¹" 		=> "Â»Ãº",
	"Â»Ãµ" 		=> "Â»Ã¶",
	"Â»Ã´" 		=> "Â»Ã²",
	"Â»Ã³" 		=> "Â»Ã±",
	"Â»Ã¯" 		=> "Â»Ã®",
	"Â»Ã¬" 		=> "Â»Ã­",
	"Â»Ã«" 		=> "Â»Ãª",
	"Â»Ã¨" 		=> "Â»Ã©",
	"Â»Ã§" 		=> "Â»Ã¥",
	"Â»Ã£" 		=> "Â»Ã¤",
	"Â»Ã¢" 		=> "Â»Ã ",
	"Â»Ã¡" 		=> "Â»Ãœ",
	"Â»Ã–" 		=> "Â»Ã‘",
	"Â»Ã‰" 		=> "Â»Ã‡",
	"Â»Ã…" 		=> "Â»Ã„",
	"Â«Ã¸" 		=> "Â«Ã¦",
	"Â«Î©" 		=> "Â«Âº",
	"Â«Âª" 		=> "Â«âˆ«",
	"Â«Ï€" 		=> "Â«âˆ",
	"Â«Âµ" 		=> "Â«Â¥",
	"Â«â‰¥" 	=> "Â«â‰¤",
	"Â«Ã˜" 		=> "Â«Ã†",
	"Â«â‰ " 	=> "Â«Â¨",
	"Â«Â´" 		=> "Â«â„¢",
	"Â«Â©" 		=> "Â«Â®",
	"Â«ÃŸ" 		=> "Â«Â¶",
	"Â«â€¢" 	=> "Â«Â§",
	"Â«Â£" 		=> "Â«Â¢",
	"Â«Â°" 		=> "Â« ",
	"Â«Ã¼" 		=> "Â«Ã»",
	"Â«Ã¹" 		=> "âˆ†Ã©",
	"Â«Ãº" 		=> "Â«Ãµ",
	"Â«Ã¶" 		=> "Â«Ã´",
	"Â«Ã²" 		=> "Â«Ã³",
	"Â«Ã±" 		=> "Â«Ã¯",
	"Â«Ã®" 		=> "Â«Ã¬",
	"Â«Ã­" 		 => "Â«Ã«",
	"Â«Ãª" 		=> "Â«Ã¨",
	"Â«Ã©" 		=> "Â«Ã§",
	"Â«Ã¥" 		=> "Â«Ã£",
	"Â«Ã¢" 		=> "Â«Ã ",
	"Â«Ãœ" 		=> "Â«Ã–",
	"âˆ†Ã¸"		=> "Â«âˆ‘",
	"âˆ†Î©" 	=> "âˆ†Âº",
	"âˆ†Ï€" 	=> "âˆ†âˆ",
	"âˆ†âˆ‚" 	=> "âˆ†Âµ",
	"âˆ†Â¥" 	=> "âˆ†â‰¥",
	"âˆ†âˆž" 	=> "âˆ†Ã˜",
	"âˆ†â‰ " 	=> "âˆ†Â¨",
	"âˆ†Â®" 	=> "âˆ†ÃŸ",
	"âˆ†â€¢"	=> "âˆ†Â§",
	"âˆ†Â£" 	=> "âˆ†Â¢",
	"âˆ†Â°" 	=> "âˆ† ",
	"âˆ†Ã»" 	=> "Â» ",
	"âˆ†Ã´" 	=> "âˆ†Ã²",
	"âˆ†Ã¯" 	=> "Â«âˆ‚",
	"âˆ†Ã­" 	 => "âˆ†Ã«",
	"âˆ†Ã¥" 	=> "âˆ†Ã£",
	"âˆ†Ã " 	=> "âˆ†Ã¡",
	"âˆ†Ã–" 	=> "âˆ†Ã‘",
	"âˆ†Ã‰" 	=> "âˆ†Ã‡",
	"â‰ˆÃ¸" 	=> "S",
	"â‰ˆÃ¦" 	=> "â‰ˆÎ©",
	"â‰ˆÂº" 	=> "â‰ˆÂª",
	"â‰ˆâˆ«" 	=> "â‰ˆÏ€",
	"â‰ˆâˆ‘" 	=> "â‰ˆâˆ‚",
	"â‰ˆÂµ" 	=> "â‰ˆÂ¥",
	"â‰ˆâ‰¥"	=> "â‰ˆâ‰¤",
	"â‰ˆÂ±" 	=> "â‰ˆâˆž",
	"â‰ˆÃ˜" 	=> "â‰ˆÃ†",
	"â‰ˆâ‰ " 	=> "â‰ˆÂ¨",
	"â‰ˆÂ´" 	=> "â‰ˆâ„¢",
	"â‰ˆÂ©" 	=> "â‰ˆÂ®",
	"â‰ˆÃŸ" 	=> "â‰ˆÂ¶",
	"â‰ˆâ€¢" 	=> "â‰ˆÂ§",
	"â‰ˆÂ£" 	=> "â‰ˆÂ¢",
	"â‰ˆÂ°" 	=> "â‰ˆ ",
	"â‰ˆÃ¼" 	=> "â‰ˆÃ»",
	"â‰ˆÃ¹" 	=> "â‰ˆÃº",
	"â‰ˆÃµ" 	=> "â‰ˆÃ¶",
	"â‰ˆÃ´" 	=> "â‰ˆÃ²",
	"â‰ˆÃ³" 	=> "â‰ˆÃ±",
	"â‰ˆÃ¯" 	=> "â‰ˆÃ®",
	"â‰ˆÃ¬" 	=> "â‰ˆÃ­",
	"â‰ˆÃ«" 	=> "â‰ˆÃª",
	"â‰ˆÃ¨" 	=> "â‰ˆÃ©",
	"â‰ˆÃ§" 	=> "â‰ˆÃ¥",
	"â‰ˆÃ£" 	=> "â‰ˆÃ¤",
	"â‰ˆÃ " 	=> "â‰ˆÃ¡",
	"â‰ˆÃœ" 	=> "â‰ˆÃ–",
	"â‰ˆÃ‘" 	=> "â‰ˆÃ‰",
	"â‰ˆÃ‡" 	=> "â‰ˆÃ…",
	"â‰ˆÃ„" 	=> "Æ’Ã¸",
	"Æ’Âº" 		=> "Æ’Âª",
	"Æ’âˆ«"		=> "Æ’Ï€",
	"Æ’âˆ‘" 	=> "Æ’âˆ‚",
	"Æ’Âµ" 		=> "Æ’Â¥",
	"Æ’â‰¥" 	=> "Æ’â‰¤",
	"Æ’Â±" 		=> "I",
	"Æ’Ã˜" 		=> "Æ’Ã†",
	"Æ’â‰ "		=> "Æ’Â¨",
	"Æ’Â´" 		=> "Æ’â„¢",
	"Æ’Â©" 		=> "Æ’Â®",
	"Æ’ÃŸ" 		=> "Æ’Â¶",
	"Æ’â€¢" 	=> "Æ’Â§",
	"Æ’Â£" 		=> "Æ’Â¢",
	"Æ’Â°" 		=> "Æ’ ",
	"Æ’Ã¼" 		=> "Æ’Ã»",
	"Æ’Ã¹" 		=> "Æ’Ãº",
	"Æ’Ãµ" 		=> "Æ’Ã¶",
	"Æ’Ã´" 		=> "Æ’Ã²",
	"Æ’Ã³" 		=> "Æ’Ã±",
	"Æ’Ã¯" 		=> "Æ’Ã®",
	"Æ’Ã¬" 		=> "Æ’Ã­",
	"Æ’Ã«" 		=> "Æ’Ãª",
	"Æ’Ã¨" 		=> "Æ’Ã©",
	"Æ’Ã§" 		=> "Æ’Ã¥",
	"Æ’Ã£" 		=> "Æ’Ã¤",
	"Æ’Ã¢" 		=> "Æ’Ã ",
	"Æ’Ã¡" 		=> "Æ’Ãœ",
	"Æ’Ã–" 		=> "Æ’Ã‘",
	"Æ’Ã‰" 		=> "Æ’Ã‡",
	"Æ’Ã…" 		=> "Æ’Ã„",
	"âˆšÃ¸"		=> "â‰ˆâˆ",
	"âˆšÃ¦" 	=> "âˆšÃ»",
	"âˆšÎ©" 	=> "âˆšÃ¹",
	"âˆšÂº" 	=> "âˆšÃº",
	"âˆšÂª" 	=> "âˆšÃµ",
	"âˆšâˆ«" 	=> "âˆšÃ¶",
	"âˆšÏ€" 	=> "âˆšÃ´",
	"âˆšâˆ" 	 => "âˆšÃ²",
	"âˆšâˆ‚" 	=> "âˆšÃ±",
	"âˆšÂµ" 	=> "âˆšÃ¯",
	"âˆšÂ¥" 	=> "âˆšÃ®",
	"âˆšâ‰¥" 	=> "âˆšÃ¬",
	"âˆšâ‰¤" 	=> "âˆšÃ­",
	"âˆšÂ±" 	=> "âˆšÃ«",
	"âˆšâˆž" 	=> "âˆšÃª",
	"âˆšÃ˜" 	=> "âˆšÃ¨",
	"âˆšÃ†" 	=> "âˆšÃ©",
	"âˆšâ‰ " 	=> "âˆšÃ§",
	"âˆšÂ¨" 	=> "âˆšÃ¥",
	"âˆšÂ´" 	=> "âˆšÃ£",
	"âˆšâ„¢" 	=> "âˆšÃ¤",
	"âˆšÂ©" 	=> "âˆšÃ¢",
	"âˆšÂ®" 	=> "âˆšÃ ",
	"âˆšÃŸ" 	=> "âˆšÃ¡",
	"âˆšÂ¶" 	=> "âˆšÃœ",
	"âˆšâ€¢" 	=> "âˆšÃ–",
	"âˆšÂ§" 	=> "âˆšÃ‘",
	"âˆšÂ£" 	=> "âˆšÃ‰",
	"âˆšÂ¢" 	=> "âˆšÃ‡",
	"âˆšÂ°" 	=> "âˆšÃ…",
	"âˆš " 		=> "âˆšÃ„",
	"Â¬Âµ" 		=> "Å’Ãº",
	"z" 		=> "Z",
	"y" 		=> "Y",
	"x" 		=> "X",
	"w" 		=> "W",
	"v" 		=> "V",
	"u" 		=> "U",
	"t" 		=> "T",
	"s" 		=> "S",
	"r" 		=> "R",
	"q" 		=> "Q",
	"p" 		=> "P",
	"o" 		=> "O",
	"n" 		=> "N",
	"m" 		=> "M",
	"l" 		=> "L",
	"k" 		=> "K",
	"j" 		=> "J",
	"i" 		=> "I",
	"h" 		=> "H",
	"g" 		=> "G",
	"f" 		=> "F",
	"e" 		=> "E",
	"d" 		=> "D",
	"c" 		=> "C",
	"b" 		=> "B",
	"a" 		=> "A",
);
