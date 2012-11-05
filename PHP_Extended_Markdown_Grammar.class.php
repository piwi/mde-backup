<?php
/**
 *
 * This file was automatically generated with PHP_Extended_Markdown_Builder class on 2012-11-05 22:32:25
 *
 * PHP Extended Markdown
 * Copyright (c) 2012 Pierre Cassat
 *
 * original MultiMarkdown
 * Copyright (c) 2005-2009 Fletcher T. Penney
 * <http://fletcherpenney.net/>
 *
 * original PHP Markdown & Extra
 * Copyright (c) 2004-2012 Michel Fortin  
 * <http://michelf.com/projects/php-markdown/>
 *
 * original Markdown
 * Copyright (c) 2004-2006 John Gruber  
 * <http://daringfireball.net/projects/markdown/>
 *
 * @package 	PHP_Extended_Markdown
 * @license   	BSD
 * @link      	https://github.com/PieroWbmstr/Extended_Markdown
 */

/**
 * PHP Extended Markdown Grammar Class
 */
class PHP_Extended_Markdown_Grammar
{

	/**
	 * Predefined urls and titles for reference links and images.
	 */
	var $predef_urls = array();
	var $predef_titles = array();
	var $predef_attributes = array();

	/**
	 * Internal hashes used during transformation.
	 */
	protected $urls = array();
	protected $titles = array();
	protected $attributes = array();
	protected $ids = array();
	
// ----------------------------------
// CONSTRUCTOR
// ----------------------------------
	
	/**
	 * Constructor function. Initialize the parser object.
	 */
	public function PHP_Extended_Markdown_Grammar() 
	{
		$this->setOption(
			'nested_brackets_re', 
			str_repeat('(?>[^\[\]]+|\[', $this->getOption('nested_brackets_depth')).
			str_repeat('\])*', $this->getOption('nested_brackets_depth'))
		);
		$this->setOption(
			'nested_url_parenthesis_re', 
			str_repeat('(?>[^()\s]+|\(', $this->getOption('nested_url_parenthesis_depth')).
			str_repeat('(?>\)))*', $this->getOption('nested_url_parenthesis_depth'))
		);
		$this->setOption(
			'escape_chars_re', 
			'['.preg_quote($this->getOption('escape_chars')).']'
		);
	}
	
	/**
	 * Setting up Extra-specific variables and run setupGamuts
	 */
	protected function setup() 
	{
		$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->attributes = $this->predef_attributes;
		$this->runGamut('setupGamut');
	}
	
	/**
	 * Clearing Extra-specific variables and run teardownGamuts
	 */
	protected function teardown() 
	{
		$this->in_stack=false;
		$this->urls = array();
		$this->titles = array();
		$this->attributes = array();
		$this->runGamut('teardownGamut');
	}
	

// -----------------------------------
// ABBREVIATIONS
// -----------------------------------

	/**
	 * Predefined abbreviations.
	 */
	var $predef_abbr = array();

	/**
	 * Extra variables used during extra transformations.
	 */
	protected $abbr_desciptions = array();
	protected $abbr_word_re = '';
	
	protected function _setupAbbreviations() 
	{
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		foreach ($this->predef_abbr as $abbr_word => $abbr_desc) {
			if ($this->abbr_word_re)
				$this->abbr_word_re .= '|';
			$this->abbr_word_re .= preg_quote($abbr_word);
			$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		}
	}

	protected function _teardownAbbreviations() 
	{
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
	}

	/**
	 * Find defined abbreviations in text and wrap them in <abbr> elements.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doAbbreviations_callback()
	 */
	public function doAbbreviations($text) 
	{
		if ($this->abbr_word_re) {
			// cannot use the /x modifier because abbr_word_re may 
			// contain significant spaces:
			$text = preg_replace_callback('{'.
				'(?<![\w\x1A])'.
				'(?:'.$this->abbr_word_re.')'.
				'(?![\w\x1A])'.
				'}', 
				array(&$this, '_doAbbreviations_callback'), $text);
		}
		return $text;
	}

	/**
	 * Process each abbreviation
	 *
	 * @param array $matches One set of results form the `doAbbreviations()` function
	 * @return string The abbreviation entry parsed
	 * @see hashPart()
	 * @see encodeAttribute()
	 */
	protected function _doAbbreviations_callback($matches) 
	{
		$abbr = $matches[0];
		if (isset($this->abbr_desciptions[$abbr])) {
			$desc = $this->abbr_desciptions[$abbr];
			if (!empty($desc)) {
				$desc = $this->encodeAttribute($desc);
//				return $this->hashPart('<abbr>'.$abbr.'</abbr>');
//			} else {
//				$desc = $this->encodeAttribute($desc);
//				return $this->hashPart('<abbr title="'.$desc.'">'.$abbr.'</abbr>');
			}
			return $this->hashPart(
				$this->runFormaterMethod('buildAbbreviation', $abbr, array('title'=>$desc))
			);

		} else {
			return $matches[0];
		}
	}

	/**
	 * Strips abbreviations from text, stores titles in hash references.
	 *
	 * Link defs are in the form: [id]*: url "optional title"
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _stripAbbreviations_callback()
	 */
	public function stripAbbreviations($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		return preg_replace_callback('{
				^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
				(.*)					# text = $2 (no blank lines allowed)	
			}xm',
			array(&$this, '_stripAbbreviations_callback'),
			$text);
	}

	/**
	 * Strips abbreviations from text, stores titles in hash references.
	 *
	 * @param array $matches Results from the `stripAbbreviations()` function
	 * @return string The text parsed
	 */
	protected function _stripAbbreviations_callback($matches) 
	{
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		if ($this->abbr_word_re)
			$this->abbr_word_re .= '|';
		$this->abbr_word_re .= preg_quote($abbr_word);
		$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		return ''; // String that will replace the block
	}
	
// -----------------------------------
// ANCHORS
// -----------------------------------

	/**
	 * Status flag to avoid invalid nesting.
	 */
	protected $in_anchor = false;
	
	protected function _setupAnchors() 
	{
		$this->in_anchor = false;
	}

	/**
	 * Turn Markdown link shortcuts into XHTML <a> tags.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doAnchors_reference_callback()
	 * @see _doAnchors_inline_callback()
	 * @see _doAnchors_reference_callback()
	 */
	public function doAnchors($text) 
	{
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;
		
		// First, handle reference-style links: [link text] [id]
		$text = preg_replace_callback('{
			(					                        # wrap whole match in $1
			  \[
				('.$this->getOption('nested_brackets_re').')	# link text = $2
			  \]

			  [ ]?				                    # one optional space
			  (?:\n[ ]*)?		                  # one optional newline followed by spaces

			  \[
				(.*?)		                        # id = $3
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		// Next, inline-style links: [link text](url "optional title")
		$text = preg_replace_callback('{
			(				                                    # wrap whole match in $1
			  \[
				('.$this->getOption('nested_brackets_re').')	          # link text = $2
			  \]
			  \(			                                  # literal paren
				[ \n]*
				(?:
					<(.+?)>	                                # href = $3
				|
					('.$this->getOption('nested_url_parenthesis_re').')	# href = $4
				)
				[ \n]*
				(			                                    # $5
				  ([\'"])	                                # quote char = $6
				  (.*?)		                                # Title = $7
				  \6		                                  # matching quote
				  [ \n]*	                                # ignore any spaces/tabs between closing quote and )
				)?			                                  # title is optional
			  \)
			)
			}xs',
			array(&$this, '_doAnchors_inline_callback'), $text);

		// Last, handle reference-style shortcuts: [link text]
		// These must come last in case you've also got [link text][1]
		// or [link text](/foo)
		$text = preg_replace_callback('{
			(					      # wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}

	/**
	 * @param array $matches A set of results of the `doAnchors` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	protected function _doAnchors_reference_callback($matches) 
	{
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == '') {
			// for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}
		
		// lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeAttribute($url);

			$attrs=array( 'href'=>$url );			
//			$result = '<a href="'.$url.'"';
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
//				$result .=  ' title="'.$title.'"';
				$attrs['title'] = $title;
			}
			if (isset($this->attributes[$link_id])) {
//				$result .= $this->doAttributes( $this->attributes[$link_id] );
				$attrs[] = $this->doAttributes( $this->attributes[$link_id] );
			}
		
			$link_text = $this->runGamut( 'spanGamut', $link_text );
//			$result .= '>'.$link_text.'</a>';
//			$result = $this->hashPart($result);
			$result = $this->hashPart(
				$this->runFormaterMethod('buildAnchor', $link_text, $attrs)
			);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}

	/**
	 * @param array $matches A set of results of the `doAnchors` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	protected function _doAnchors_inline_callback($matches) 
	{
		$whole_match	=  $matches[1];
		$link_text		=  $this->runGamut( 'spanGamut', $matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$url = $this->encodeAttribute($url);

			$attrs=array( 'href'=>$url );			
//		$result = '<a href="'.$url.'"';
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
//			$result .=  ' title="'.$title.'"';
			$attrs['title'] = $title;
		}
		$link_text = $this->runGamut( 'spanGamut', $link_text);
//		$result .= '>'.$link_text.'</a>';

//		return $this->hashPart($result);
//			$result = $this->hashPart($result);
		return $this->hashPart(
			$this->runFormaterMethod('buildAnchor', $link_text, $attrs)
		);
	}

// -----------------------------------
// AUTOMATIC LINKS
// -----------------------------------

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doAutoLinks_url_callback()
	 * @see _doAutoLinks_email_callback()
	 */
	public function doAutoLinks($text) 
	{
		$text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', 
			array(&$this, '_doAutoLinks_url_callback'), $text);

		// Email addresses: <address@domain.foo>
		return preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array(&$this, '_doAutoLinks_email_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `doAutoLinks` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doAutoLinks_url_callback($matches) 
	{
		$url = $this->encodeAttribute($matches[1]);
//		$link = '<a href="'.$url.'">'.$url.'</a>';
//		return $this->hashPart($link);

		return $this->hashPart(
			$this->runFormaterMethod('buildLink', $url, array('href'=>$url))
		);
	}

	/**
	 * @param array $matches A set of results of the `doAutoLinks` function
	 * @return string The text parsed
	 * @see encodeEmailAddress()
	 * @see hashPart()
	 */
	protected function _doAutoLinks_email_callback($matches) 
	{
		$address = $matches[1];
//		$link = $this->encodeEmailAddress($address);
//		return $this->hashPart($link);
		return $this->hashPart(
			$this->runFormaterMethod('buildMailto', $address)
		);
	}

// -----------------------------------
// BLOCKQUOTES
// -----------------------------------

	/**
	 * Create blockquotes blocks
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doBlockQuotes_callback()
	 */
	public function doBlockQuotes($text) 
	{
		return preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?		# ">" at the start of a line
					(?:\((.+?)\))?
					.+\n					# rest of the first line
				  (.+\n)*				# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm',
			array(&$this, '_doBlockQuotes_callback'), $text);
	}

	/**
	 * Build each blockquote block
	 *
	 * @param array $matches A set of results of the `doBlockQuotes()` function
	 * @return string The text parsed
	 * @see runBlockGamut()
	 * @see _doBlockQuotes_callback2()
	 */
	protected function _doBlockQuotes_callback($matches) 
	{
		$bq = $matches[1];
		$cite = $matches[2];
		// trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?(\((.+?)\))?|^[ ]+$/m', '', $bq);
		$bq = $this->runGamut( 'blockGamut', $bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		// These leading spaces cause problem with <pre> content, 
		// so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $bq);

//		return "\n". $this->hashBlock('<blockquote'
//			.( !empty($cite) ? ' cite="'.$cite.'"' : '' )
//			.'>'."\n".$bq."\n".'</blockquote>')."\n\n";

		$attrs = array();
		if (!empty($cite))
		{
			$attrs['cite'] = $cite;
		}
		return "\n". $this->hashBlock(
			$this->runFormaterMethod('buildBlockquote', "\n".$bq."\n", $attrs)
		)."\n\n";
	}

	/**
	 * Deletes the last sapces, for <pre> blocks
	 *
	 * @param array $matches A set of results of the `_doBlockQuotes_callback()` function
	 * @return string The text parsed
	 */
	protected function _doBlockQuotes_callback2($matches) 
	{
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}

// -----------------------------------
// CODE BLOCKS
// -----------------------------------

	/**
	 *	Process Markdown `<pre><code>` blocks.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doCodeBlocks_callback()
	 */
	public function doCodeBlocks($text) 
	{
		return preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	                                      # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->getOption('tab_width').'}             # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->getOption('tab_width').'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array(&$this, '_doCodeBlocks_callback'), $text);
	}

	/**
	 * Build `<pre><code>` blocks.
	 *
	 * @param array $matches A set of results of the `doCodeBlocks()` function
	 * @return string Text parsed
	 * @see hashBlock()
	 */
	protected function _doCodeBlocks_callback($matches) 
	{
		$codeblock = $matches[1];

		$codeblock = $this->doOutdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

//		$codeblock = '<pre><code>'.$codeblock."\n".'</code></pre>';
//		return "\n\n".$this->hashBlock($codeblock)."\n\n";

		return "\n\n".$this->hashBlock(
			$this->runFormaterMethod('buildCodeBlock', $codeblock."\n")
		)."\n\n";
	}

	/**
	 * Create a code span markup for $code. Called from handleSpanToken.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see hashPart()
	 */
	public function makeCodeSpan($code) 
	{
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
//		return $this->hashPart('<code>'.$code.'</code>');
		return $this->hashPart(
			$this->runFormaterMethod('buildCodeSpan', $code)
		);
	}

// ----------------------------------
// FENCED CODE BLOCK
// ----------------------------------
	
	/**
	 * Adding the fenced code block syntax to regular Markdown:
	 *
	 *     ~~~
	 *     Code block
	 *     ~~~
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doFencedCodeBlocks_callback()
	 */
	public function doFencedCodeBlocks($text) 
	{
		return preg_replace_callback('{
				(?:\n|\A)           # 1: Opening marker
				(
					~{3,}             # Marker: three tilde or more.
				)
				(\w+)?              # 2: Language
				[ ]* \n             # Whitespace and newline following marker.
				(                   # 3: Content
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				\1 [ ]* \n          # Closing marker
			}xm',
			array(&$this, '_doFencedCodeBlocks_callback'), $text);
	}

	/**
	 * Process the fenced code blocks
	 *
	 * @param array $matches Results form the `doFencedCodeBlocks()` function
	 * @return string The text parsed
	 * @see _doFencedCodeBlocks_newlines()
	 * @see hashBlock()
	 */
	protected function _doFencedCodeBlocks_callback($matches) 
	{
		$codeblock = $matches[3];
		$language  = $matches[2];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback('/^\n+/', array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);
/*
		$codeblock = '<pre><code'
			.( !empty($language) ? ' class="language-'.$language.'"' : '' )
			.'>'.$codeblock.'</code></pre>';
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
*/
		$attrs = array();
		if (!empty($language))
		{
			$attrs['class'] = 'language-'.$language;
		}
		return "\n\n".$this->hashBlock(
			$this->runFormaterMethod('buildCodeBlock', $codeblock, $attrs)
		)."\n\n";
	}

	/**
	 * Process the fenced code blocks new lines
	 *
	 * @param array $matches Results form the `doFencedCodeBlocks()` function (passed from the `_doFencedCodeBlocks_callback()` function)
	 * @return string The block parsed
	 */
	protected function _doFencedCodeBlocks_newlines($matches) 
	{
		return str_repeat( '<br'.$this->getOption('empty_element_suffix'), strlen($matches[0]) );
	}

// -----------------------------------
// DEFINITIONS LISTS
// -----------------------------------

	/**
	 * Form HTML definition lists.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doDefinitionsLists_callback()
	 */
	public function doDefinitionsLists($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Re-usable pattern to match any entire dl list:
		$whole_list_re = '(?>
			(								                    # $1 = whole list
			  (								                  # $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				            # $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+    # colon starting definition
			  )
			  (?s:.+?)
			  (								                  # $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						                 # Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			           # defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						                 # Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; // mx

		return preg_replace_callback('{
				(?>\A\n?|(?<=\n\n))
				'.$whole_list_re.'
			}mx',
			array(&$this, '_doDefinitionsLists_callback'), $text);
	}

	/**
	 * Turn double returns into triple returns, so that we can make a
	 * paragraph for the last item in a list, if necessary
	 *
	 * @param array $matches The results form the doDefinitionsLists()` `preg_replace_callback()` command
	 * @return function Pass its result to the `hashBlock()` function
	 * @see hashBlock()
	 * @see doDefinitionsLists()
	 * @see processDefinitionsListItems()
	 */
	protected function _doDefinitionsLists_callback($matches) 
	{
		// Re-usable patterns to match list item bullets and number markers:
		$list = $matches[1];
		$result = trim($this->processDefinitionsListItems($list));
//		$result = '<dl>'."\n" . $result . "\n".'</dl>';
//		return $this->hashBlock($result) . "\n\n";

		return $this->hashBlock(
			$this->runFormaterMethod('buildDefinitionList', "\n".$result."\n")
		)."\n\n";
	}


	/**
	 * Process the contents of a single definition list, splitting it
	 * into individual term and definition list items.
	 *
	 * @param string $list_str The result string form the _doDefinitionsLists_callback()` function
	 * @return string Parsed list string
	 * @see _doDefinitionsLists_callback()
	 * @see _processDefinitionsListItems_callback_dt()
	 * @see _processDefinitionsListItems_callback_dd()
	 */
	public function processDefinitionsListItems($list_str) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		
		// trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		// Process definition terms.
		$list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					    # leading line
			(								            # definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?![:][ ]|[ ])				    # negative lookahead for a definition 
											            # mark (colon) or more whitespace.
				(?> \S.* \n)+?				    # actual term (not whitespace).	
			)			
			(?=\n?[ ]{0,3}:[ ])				  # lookahead for following line feed 
											            # with a definition mark.
			}xm',
			array(&$this, '_processDefinitionsListItems_callback_dt'), $list_str);

		// Process actual definitions.
		$list_str = preg_replace_callback('{
			\n(\n+)?						        # leading line = $1
			(								            # marker space = $2
				[ ]{0,'.$less_than_tab.'}	# whitespace before colon
				[:][ ]+						        # definition mark (colon)
			)
			((?s:.+?))					 	      # definition text = $3
			(?= \n+ 						        # stop at next definition mark,
				(?:							          # next term or end of text
					[ ]{0,'.$less_than_tab.'} [:][ ]	|
					<dt> | \z
				)						
			)					
			}xm',
			array(&$this, '_processDefinitionsListItems_callback_dd'), $list_str);

		return $list_str;
	}

	/**
	 * Process the dt contents.
	 *
	 * @param array $matches The results form the `processDefinitionsListItems()` function
	 * @return string Parsed dt string
	 * @see processDefinitionsListItems()
	 * @see runSpanGamut()
	 */
	protected function _processDefinitionsListItems_callback_dt($matches) 
	{
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		foreach ($terms as $term) {
			$term = $this->runGamut( 'spanGamut', trim($term) );
//			$text .= "\n".'<dt>' . $term . '</dt>';
			$text .= "\n".$this->runFormaterMethod('buildDefinitionTerm', $term);
		}
		return $text . "\n";

	}

	/**
	 * Process the dd contents.
	 *
	 * @param array $matches The results form the `processDefinitionsListItems()` function
	 * @return string Parsed dd string
	 * @see processDefinitionsListItems()
	 * @see runSpanGamut()
	 */
	protected function _processDefinitionsListItems_callback_dd($matches) 
	{
		$leading_line	= $matches[1];
		$marker_space	= $matches[2];
		$def			= $matches[3];

		if ($leading_line || preg_match('/\n{2,}/', $def)) {
			// Replace marker with the appropriate whitespace indentation
			$def = str_repeat(' ', strlen($marker_space)) . $def;
			$def = $this->runGamut( 'blockGamut', $this->doOutdent($def . "\n\n"));
			$def = "\n". $def ."\n";
		}
		else {
			$def = rtrim($def);
			$def = $this->runGamut( 'spanGamut', $this->doOutdent($def));
		}

//		return "\n".'<dd>' . $def . '</dd>'."\n";
		return "\n".$this->runFormaterMethod('buildDefinitionDescription', $def)."\n";
	}

// -----------------------------------
// DETAB
// -----------------------------------

	/**
	 * String length function for detab. `_initDetab` will create a function to 
	 * hanlde UTF-8 if the default function does not exist.
	 */
	var $utf8_strlen = 'mb_strlen';
	
	/**
	 * Replace tabs with the appropriate amount of space.
	 *
	 * For each line we separate the line in blocks delemited by
	 * tab characters. Then we reconstruct every line by adding the 
	 * appropriate number of space between each blocks.
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _detab_callback()
	 */
	public function doDetab($text) 
	{
		$text = preg_replace_callback('/^.*\t.*$/m', array(&$this, '_detab_callback'), $text);
		return $text;
	}

	/**
	 * Process tabs replacement
	 *
	 * @param array $matches A set of results of the `doDetab()` function
	 * @return string The line rebuilt
	 */
	protected function _detab_callback($matches) 
	{
		$line = $matches[0];
		$strlen = $this->utf8_strlen; // strlen function for UTF-8.
		
		// Split in blocks.
		$blocks = explode("\t", $line);
		// Add each blocks to the line.
		$line = $blocks[0];
		unset($blocks[0]); // Do not add first block twice.
		foreach ($blocks as $block) {
			// Calculate amount of space, insert spaces, insert block.
			$amount = $this->getOption('tab_width') - $strlen($line, 'UTF-8') % $this->getOption('tab_width');
			$line .= str_repeat(' ', $amount) . $block;
		}
		return $line;
	}

	/**
	 * Check for the availability of the function in the `utf8_strlen` property
	 * (initially `mb_strlen`). If the function is not available, create a 
	 * function that will loosely count the number of UTF-8 characters with a
	 * regular expression.
	 */
	protected function _initDetab() 
	{
		if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);');
	}

// -----------------------------------
// EMPHASIS
// -----------------------------------

	/**#@+
	 * Redefining emphasis markers so that emphasis by underscore does not
	 * work in the middle of a word.
	 */
	protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\S|$)(?![\.,:;]\s)',
		'*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
		'_' => '(?<=\S|^)(?<!_)_(?![a-zA-Z0-9_])',
	);

	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\S|$)(?![\.,:;]\s)',
		'**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
		'__' => '(?<=\S|^)(?<!_)__(?![a-zA-Z0-9_])',
	);

	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<![a-zA-Z0-9_])___(?!_))(?=\S|$)(?![\.,:;]\s)',
		'***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
		'___' => '(?<=\S|^)(?<!_)___(?![a-zA-Z0-9_])',
	);

	protected $em_strong_prepared_relist;
	/**#@-*/

	/**
	 * Prepare regular expressions for searching emphasis tokens in any context.
	 */
	public function prepareEmphasis() 
	{
		foreach ($this->em_relist as $em => $em_re) {
			foreach ($this->strong_relist as $strong => $strong_re) {
				// Construct list of allowed token expressions.
				$token_relist = array();
				$_index = (string) $em.$strong;
				if (isset($this->em_strong_relist[$_index])) {
					$token_relist[] = $this->em_strong_relist[$_index];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;
				
				// Construct master expression from list.
				$token_re = '{('. implode('|', $token_relist) .')}';
				$this->em_strong_prepared_relist[$_index] = $token_re;
			}
		}
	}
	
	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	public function doEmphasis($text) 
	{
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;
		
		while (1) {

			// Get prepared regular expression for seraching emphasis tokens in current context.
			$_index = (string) $em.$strong;
			$token_re = $this->em_strong_prepared_relist[$_index];
			
			// Each loop iteration search for the next emphasis token. 
			// Each token is then passed to handleSpanToken.
			$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token =& $parts[1];
			$text =& $parts[2];
			
			if (empty($token)) {
				// Reached end of text span: empty stack without emitting any more emphasis.
				while ($token_stack[0]) {
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}
			
			$token_len = strlen($token);
			if ($tree_char_em) {
				// Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3) {
					// Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<strong><em>'.$span.'</em></strong>';

					$span = $this->runFormaterMethod('buildEmphasis', 'both', $span);

					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				} else {
					// Other closing marker: close one em or strong and
					// change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag = $token_len == 2 ? 'strong' : 'em';
					$span = $text_stack[0];
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<'.$tag.'>'.$span.'</'.$tag.'>';

					$span = $this->runFormaterMethod('buildEmphasis', $tag, $span);

					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; // $$tag stands for $em or $strong
				}
				$tree_char_em = false;
			} else if ($token_len == 3) {
				if ($em) {
					// Reached closing marker for both em and strong.
					// Closing strong marker:
					for ($i = 0; $i < 2; ++$i) {
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? 'strong' : 'em';
						$span = array_shift($text_stack);
						$span = $this->runGamut( 'spanGamut', $span);
//						$span = '<'.$tag.'>'.$span.'</'.$tag.'>';

						$span = $this->runFormaterMethod('buildEmphasis', $tag, $span);

						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; // $$tag stands for $em or $strong
					}
				} else {
					// Reached opening three-char emphasis marker. Push on token 
					// stack; will be handled by the special condition above.
					$em = $token{0};
					$strong = (string) $em.$em;
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			} else if ($token_len == 2) {
				if ($strong) {
					// Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
					// Closing strong marker:
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<strong>'.$span.'</strong>';

					$span = $this->runFormaterMethod('buildEmphasis', 'strong', $span);

					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			} else {
				// Here $token_len == 1
				if ($em) {
					if (strlen($token_stack[0]) == 1) {
						// Closing emphasis marker:
						array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runGamut( 'spanGamut', $span);
//						$span = '<em>'.$span.'</em>';

						$span = $this->runFormaterMethod('buildEmphasis', 'em', $span);

						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					} else {
						$text_stack[0] .= $token;
					}
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}

// -----------------------------------
// HARD BREAKS
// -----------------------------------

	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _doHardBreaks_callback()
	 */
	public function doHardBreaks($text) 
	{
		// Do hard breaks:
		return preg_replace_callback('/ {2,}\n/', array(&$this, '_doHardBreaks_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `doHardBreak()` function
	 * @return string The text parsed
	 * @see hashPart()
	 */
	protected function _doHardBreaks_callback($matches) 
	{
		return $this->hashPart('<br'.$this->getOption('empty_element_suffix')."\n");
	}

// -----------------------------------
// HASHES
// -----------------------------------

	/**
	 * Internal hashes used during transformation.
	 */
	protected $html_hashes = array();

	protected function _clearHashes() 
	{
		$this->html_hashes = array();
	}

	/**
	 * Called whenever a tag must be hashed when a function insert an atomic 
	 * element in the text stream. Passing $text to through this function gives
	 * a unique text-token which will be reverted back when calling unhash.
	 *
	 * The $boundary argument specify what character should be used to surround
	 * the token. By convension, "B" is used for block elements that needs not
	 * to be wrapped into paragraph tags at the end, ":" is used for elements
	 * that are word separators and "X" is used in the general case.
	 *
	 * @param string $text The text to be parsed
	 * @param string $boundary A one letter boundary
	 * @return string The text parsed
	 * @see unhash()
	 */
	public function hashPart($text, $boundary = 'X') 
	{
		// Swap back any tag hash found in $text so we do not have to `unhash`
		// multiple times at the end.
		$text = $this->unhash($text);
		// Then hash the block.
		static $i = 0;
		$key = $boundary."\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; // String that will replace the tag.
	}

	/**
	 * Shortcut function for hashPart with block-level boundaries.
	 *
	 * @param string $text The text to be parsed
	 * @return function Pass results of the `hashPart()` function
	 * @see hashPart()
	 */
	public function hashBlock($text) 
	{
		return $this->hashPart($text, 'B');
	}

	/**
	 * Swap back in all the tags hashed by _HashHTMLBlocks.
	 *
	 * @param string $text The text to be parsed
	 * @return function Pass results of the `_unhash_callback()` function
	 * @see _unhash_callback()
	 */
	public function unhash($text) 
	{
		return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(&$this, '_unhash_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `unhash()` function
	 * @return empty
	 */
	protected function _unhash_callback($matches) 
	{
		return $this->html_hashes[$matches[0]];
	}

	/**
	 * Called whenever a tag must be hashed when a function insert a "clean" tag
	 * in $text, it pass through this function and is automaticaly escaped, 
	 * blocking invalid nested overlap.
	 *
	 * @param string $text Text to parse
	 * @return string Text parsed
	 * @see hashPart()
	 */
	public function hashClean($text) 
	{
		return $this->hashPart($text, 'C');
	}

// -----------------------------------
// HEADERS
// -----------------------------------
	
	/**
	 * Redefined to add id attribute support.
	 *
	 * Setext-style headers:
	 *	  Header 1  {#header1}
	 *	  ========
	 *  
	 *	  Header 2  {#header2}
	 *	  --------
	 *
	 * ATX-style headers:
	 *	# Header 1        {#header1}
	 *	## Header 2       {#header2}
	 *	## Header 2 with closing hashes ##  {#header3}
	 *	...
	 *	###### Header 6   {#header2}
	 *
	 * @param string $text Text to parse
	 * @return string Text with all headers parsed
	 * @see _doHeaders_callback_setext()
	 * @see _doHeaders_callback_atx()
	 */
	public function doHeaders($text) 
	{
		// Setext-style headers:
		$text = preg_replace_callback(
			'{
				(^.+?)								            # $1: Header text
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?	# $2: Id attribute
				[ ]*\n(=+|-+)[ ]*\n+				      # $3: Header footer
			}mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		// atx-style headers:
		$text = preg_replace_callback('{
				^(\#{1,6})	                     # $1 = string of #\'s
				[ ]*
				(.+?)		                         # $2 = Header text
				[ ]*
				\#*			                         # optional closing #\'s (not counted)
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? # id attribute
				[ ]*
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}

	/**
	 * Process setext-style headers:
	 *	  Header 1  {#header1}
	 *	  ========
	 *  
	 *	  Header 2  {#header2}
	 *	  --------
	 *
	 * @param array $matches The results from the `doHeaders()` function
	 * @return string Text with header parsed
	 * @see _doHeaders_attr()
	 * @see runSpanGamut()
	 * @see hashBlock()
	 */
	protected function _doHeaders_callback_setext($matches) 
	{
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$attr  = $this->_doHeaders_attr($id =& $matches[2]);
//		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";

		$block = $this->runFormaterMethod('buildHeader', 
			$this->runGamut( 'spanGamut', $matches[1] ), $level, $attr);

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * Process ATX-style headers:
	 *	# Header 1        {#header1}
	 *	## Header 2       {#header2}
	 *	## Header 2 with closing hashes ##  {#header3}
	 *	...
	 *	###### Header 6   {#header2}
	 *
	 * @param array $matches The results from the `doHeaders()` function
	 * @return string Text with header parsed
	 * @see _doHeaders_attr()
	 * @see runSpanGamut()
	 * @see hashBlock()
	 */
	protected function _doHeaders_callback_atx($matches) 
	{
		$level = strlen($matches[1]);
		if (!empty($matches[3]))
//			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$id  = $this->_doHeaders_attr($matches[3]);
		else
//			$attr  = $this->_doHeaders_attr($id =& $this->header2Label($matches[2]));
			$id  = $this->_doHeaders_attr($this->header2Label($matches[2]));
//		$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";

		$attr = array( 'id'=>$id );
		$block = $this->runFormaterMethod('buildHeader', 
			$this->runGamut( 'spanGamut', $matches[2]), $level, $attr);

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * The header builder
	 * @formater_overload
	protected function buildHeader( $text, $level=1, $attr='' )
	{
		return '<h'.$level.$attr.'>'.$text.'</h'.$level.'>';
	}
	 */

	/**
	 * Adding headers attributes if so 
	 *
	 * @param str $attr The attributes string
	 * @return string Text to add in the header tag
	 */
	protected function _doHeaders_attr($attr) 
	{
		if (empty($attr)) return '';
		$id = $attr;
		if (in_array($id, $this->ids)) {
			$i=0;
			while (in_array($id, $this->ids)) {
				$i++;
				$id = (string)$attr.$i;
			}
		}
		$this->ids[] = $id;
//		return ' id="'.$id.'"';
//		return array( 'id'=>$id );
		return $id;
	}

// -----------------------------------
// HORIZONTAL RULES
// -----------------------------------

	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see hashBlock()
	 */
	public function doHorizontalRules($text) 
	{
		// Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}	  # Leading space
				([-*_])		  # $1: First marker
				(?>			    # Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			  # Marker character
				){2,}		    # Group repeated at least twice
				[ ]*		    # Tailing spaces
				$			      # End of line.
			}mx',
			"\n".$this->hashBlock('<hr'.$this->getOption('empty_element_suffix'))."\n", 
			$text);
	}

// -----------------------------------
// HTML BLOCKS
// -----------------------------------

	/**
	 * Hashify HTML Blocks and "clean tags".
	 *
	 * We only want to do this for block-level HTML tags, such as headers,
	 * lists, and tables. That's because we still want to wrap <p>s around
	 * "paragraphs" that are wrapped in non-block-level tags, such as anchors,
	 * phrase emphasis, and spans. The list of tags we're looking for is
	 * hard-coded.
	 *
	 * This works by calling _HashHTMLBlocks_InMarkdown, which then calls
	 * _HashHTMLBlocks_InHTML when it encounter block tags. When the markdown="1" 
	 * attribute is found whitin a tag, _HashHTMLBlocks_InHTML calls back
	 *  _HashHTMLBlocks_InMarkdown to handle the Markdown syntax within the tag.
	 * These two functions are calling each other. It's recursive!
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _hashHTMLBlocks_inMarkdown()
	 */
	public function hashHTMLBlocks($text) 
	{
		// Call the HTML-in-Markdown hasher.
		list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);
		return $text;
	}

	/**
	 * Parse markdown text, calling _HashHTMLBlocks_InHTML for block tags.
	 *
	 * *   $indent is the number of space to be ignored when checking for code 
	 *     blocks. This is important because if we don't take the indent into 
	 *     account, something like this (which looks right) won't work as expected:
	 *
	 *     <div>
	 *         <div markdown="1">
	 *         Hello World.  <-- Is this a Markdown code block or text?
	 *         </div>  <-- Is this a Markdown code block or a real tag?
	 *     <div>
	 *
	 *     If you don't like this, just don't indent the tag on which
	 *     you apply the markdown="1" attribute.
	 *
	 * *   If $enclosing_tag_re is not empty, stops at the first unmatched closing 
	 *     tag with that name. Nested tags supported.
	 *
	 * *   If $span is true, text inside must treated as span. So any double 
	 *     newline will be replaced by a single newline so that it does not create 
	 *     paragraphs.
	 *
	 * Returns an array of that form: ( processed text , remaining text )
	 *
	 * @param string $text The text to be parsed
	 * @param int $indent The indentation to use
	 * @param string $enclosing_tag_re The closing tag to use
	 * @param bool $span Are we in a span element (false by default)
	 * @return array ( processed text , remaining text )
	 * @see hashPart()
	 * @see _hashHTMLBlocks_inHTML()
	 */
	protected function _hashHTMLBlocks_inMarkdown($text, $indent = 0, $enclosing_tag_re = '', $span = false)
	{
		if ($text === '') return array('', '');

		// Regex to check for the presense of newlines around a block tag.
		$newline_before_re = '/(?:^\n?|\n\n)*$/';
		$newline_after_re = 
			'{
				^						          # Start of text following the tag.
				(?>[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					      # Must be followed by newline.
			}xs';
		
		// Regex to match any tag.
		$block_tag_re =
			'{
				(					            # $2: Capture hole tag.
					</?					        # Any opening or closing tag.
						(?>				        # Tag name.
							'.$this->getOption('block_tags_re').'			|
							'.$this->getOption('context_block_tags_re').'	|
							'.$this->getOption('clean_tags_re').'        	|
							(?!\s)'.$enclosing_tag_re.'
						)
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	    # Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				    # Anything but quotes and `>`.
							)*?
						)?
					>					          # End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				|
					                    # Code span marker
					`+
				'. ( !$span ? '       # If not in span.
				|
					                    # Indented code block
					(?: ^[ ]*\n | ^ | \n[ ]*\n )
					[ ]{'.($indent+4).'}[^\n]* \n
					(?>
						(?: [ ]{'.($indent+4).'}[^\n]* | [ ]* ) \n
					)*
				|
					                   # Fenced code block marker
					(?> ^ | \n )
					[ ]{0,'.($indent).'}~~~+[ ]*\n
				' : '' ). '          # End (if not is span).
				)
			}xs';

		
		$depth = 0;		// Current depth inside the tag tree.
		$parsed = '';	// Parsed text that will be returned.

		// Loop through every tag until we find the closing tag of the parent
		// or loop until reaching the end of text if no parent tag specified.
		do {

			// Split the text using the first $tag_match pattern found.
			// Text before  pattern will be first in the array, text after
			// pattern will be at the end, and between will be any catches made 
			// by the pattern.
			$parts = preg_split($block_tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			// If in Markdown span mode, add a empty-string span-level hash 
			// after each newline to prevent triggering any block element.
			if ($span) {
				$void = $this->hashPart('', ':');
				$newline = $void."\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}
			
			$parsed .= $parts[0]; // Text before current tag.
			
			// If end of $text has been reached. Stop loop.
			if (count($parts) < 3) {
				$text = '';
				break;
			}
			
			$tag  = $parts[1]; // Tag to handle.
			$text = $parts[2]; // Remaining text after current tag.
			$tag_re = preg_quote($tag); // For use in a regular expression.
			
			// Check for: Code span marker
			if ($tag{0} == '`') {
				// Find corresponding end marker.
				$tag_re = preg_quote($tag);
				// End marker found: pass text unchanged until marker.
				if (preg_match('{^(?>.+?|\n(?!\n))*?(?<!`)'.$tag_re.'(?!`)}', $text, $matches)) {
					$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));

				// Unmatched marker: just skip it.
				} else {
					$parsed .= $tag;
				}
			}

			// Check for: Fenced code block marker.
			else if (preg_match('{^\n?[ ]{0,'.($indent+3).'}~}', $tag)) {
				// Fenced code block marker: find matching end marker.
				$tag_re = preg_quote(trim($tag));
				// End marker found: pass text unchanged until marker.
				if (preg_match('{^(?>.*\n)+?[ ]{0,'.$indent.'}'.$tag_re.'[ ]*\n}', $text, $matches)) {
					$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));

				// No end marker: just skip it.
				} else {
					$parsed .= $tag;
				}
			}

			// Check for: Indented code block.
			else if ($tag{0} == "\n" || $tag{0} == ' ') {
				// Indented code block: pass it unchanged, will be handled later.
				$parsed .= $tag;
			}

			// Check for: Opening Block level tag or
			//            Opening Context Block tag (like ins and del) 
			//               used as a block tag (tag is alone on it's line).
			else if (preg_match('{^<(?:'.$this->getOption('block_tags_re').')\b}', $tag) ||
				(	preg_match('{^<(?:'.$this->getOption('context_block_tags_re').')\b}', $tag) &&
					preg_match($newline_before_re, $parsed) &&
					preg_match($newline_after_re, $text)	)
				)
			{
				// Need to parse tag and following text using the HTML parser.
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, 'hashBlock', true);
				
				// Make sure it stays outside of any paragraph by adding newlines.
				$parsed .= "\n\n".$block_text."\n\n";
			}

			// Check for: Clean tag (like script, math)
			//            HTML Comments, processing instructions.
			else if (preg_match('{^<(?:'.$this->getOption('clean_tags_re').')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				// Need to parse tag and following text using the HTML parser.
				// (don't check for markdown attribute)
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, 'hashClean', false);
				
				$parsed .= $block_text;
			}

			// Check for: Tag with same name as enclosing tag.
			else if ($enclosing_tag_re !== '' &&
				# Same name as enclosing tag.
				preg_match('{^</?(?:'.$enclosing_tag_re.')\b}', $tag))
			{

				// Increase/decrease nested tag count.
				if ($tag{1} == '/')						        $depth--;
				else if ($tag{strlen($tag)-2} != '/')	$depth++;

				if ($depth < 0) {
					// Going out of parent element. Clean up and break so we
					// return to the calling function.
					$text = $tag . $text;
					break;
				}
				
				$parsed .= $tag;
			}
			else {
				$parsed .= $tag;
			}
		} while ($depth >= 0);
		
		return array($parsed, $text);
	}

	/**
	 * Parse HTML, calling _HashHTMLBlocks_InMarkdown for block tags.
	 *
	 * *   Calls $hash_method to convert any blocks.
	 * *   Stops when the first opening tag closes.
	 * *   $md_attr indicate if the use of the `markdown="1"` attribute is allowed.
	 *     (it is not inside clean tags)
	 *
	 * Returns an array of that form: ( processed text , remaining text )
	 *
	 * @param string $text The text to be parsed
	 * @param string $hash_method The method to execute
	 * @param string $md_attr The attributes to add
	 * @return array ( processed text , remaining text )
	 * @see _hashHTMLBlocks_inMarkdown()
	 */
	protected function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) 
	{
		if ($text === '') return array('', '');
		
		// Regex to match `markdown` attribute inside of a tag.
		$markdown_attr_re = '
			{
				\s*           # Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?>
					(["\'])		 # $1: quote delimiter		
					(.*?)		   # $2: attribute value
					\1			   # matching delimiter	
				|
					([^\s>]*)	 # $3: unquoted attribute value
				)
				()				   # $4: make $3 always defined (avoid warnings)
			}xs';
		
		// Regex to match any tag.
		$tag_re = '{
				(					            # $2: Capture hole tag.
					</?					        # Any opening or closing tag.
						[\w:$]+			      # Tag name.
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	    # Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				    # Anything but quotes and `>`.
							)*?
						)?
					>					          # End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';
		
		$original_text = $text;	// Save original text in case of faliure.
		
		$depth		= 0;	  // Current depth inside the tag tree.
		$block_text	= '';	// Temporary text holder for current text.
		$parsed		= '';	  // Parsed text that will be returned.

		// Get the name of the starting tag.
		// (This pattern makes $base_tag_name_re safe without quoting.)
		if (preg_match('/^<([\w:$]*)\b/', $text, $matches))
			$base_tag_name_re = $matches[1];

		// Loop through every tag until we find the corresponding closing tag.
		do {

			// Split the text using the first $tag_match pattern found.
			// Text before  pattern will be first in the array, text after
			// pattern will be at the end, and between will be any catches made 
			// by the pattern.
			$parts = preg_split($tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			if (count($parts) < 3) {
				// End of $text reached with unbalenced tag(s).
				// In that case, we return original text unchanged and pass the
				// first character as filtered to prevent an infinite loop in the 
				// parent function.
				return array($original_text{0}, substr($original_text, 1));
			}
			
			$block_text .= $parts[0]; // Text before current tag.
			$tag         = $parts[1]; // Tag to handle.
			$text        = $parts[2]; // Remaining text after current tag.
			
			// Check for: Auto-close tag (like <hr/>) Comments and Processing Instructions.
			if (preg_match('{^</?(?:'.$this->getOption('auto_close_tags_re').')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				// Just add the tag to the block as if it was text.
				$block_text .= $tag;
			}
			else {

				// Increase/decrease nested tag count. Only do so if
				// the tag's name match base tag's.
				if (preg_match('{^</?'.$base_tag_name_re.'\b}', $tag)) {
					if ($tag{1} == '/')						$depth--;
					else if ($tag{strlen($tag)-2} != '/')	$depth++;
				}
				
				// Check for `markdown="1"` attribute and handle it.
				if ($md_attr && 
					preg_match($markdown_attr_re, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
					// Remove `markdown` attribute from opening tag.
					$tag = preg_replace($markdown_attr_re, '', $tag);
					
					// Check if text inside this tag must be parsed in span mode.
					$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match('{^<(?:'.$this->getOption('contain_span_tags_re').')\b}', $tag);
					
					// Calculate indent before tag.
					if (preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches)) {
						$strlen = $this->utf8_strlen;
						$indent = $strlen($matches[1], 'UTF-8');
					} else {
						$indent = 0;
					}
					
					// End preceding block with this tag.
					$block_text .= $tag;
					$parsed     .= $this->$hash_method($block_text);
					
					// Get enclosing tag name for the ParseMarkdown function.
					// (This pattern makes $tag_name_re safe without quoting.)
					preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name_re = $matches[1];
					
					// Parse the content using the HTML-in-Markdown parser.
					list ($block_text, $text) = 
						$this->_hashHTMLBlocks_inMarkdown($text, $indent, $tag_name_re, $span_mode);
					
					// Outdent markdown text.
					if ($indent > 0) {
						$block_text = preg_replace('/^[ ]{1,'.$indent.'}/m', '', $block_text);
					}
					
					// Append tag content to parsed text.
					if (!$span_mode)	$parsed .= "\n\n".$block_text."\n\n";
					else				$parsed .= $block_text;
					
					// Start over a new block.
					$block_text = '';
				}
				else $block_text .= $tag;
			}
			
		} while ($depth > 0);
		
		// Hash last block text that wasn't processed inside the loop.
		$parsed .= $this->$hash_method($block_text);
		
		return array($parsed, $text);
	}

// -----------------------------------
// IMAGES
// -----------------------------------

	/**
	 * Turn Markdown image shortcuts into <img> tags.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doImages_reference_callback()
	 * @see _doImages_inline_callback()
	 */
	public function doImages($text) 
	{
		// First, handle reference-style labeled images: ![alt text][id]
		$text = preg_replace_callback('{
			(				                            # wrap whole match in $1
			  !\[
				('.$this->getOption('nested_brackets_re').')		# alt text = $2
			  \]

			  [ ]?				                      # one optional space
			  (?:\n[ ]*)?		                    # one optional newline followed by spaces

			  \[
				(.*?)		                          # id = $3
			  \]

			)
			}xs', 
			array(&$this, '_doImages_reference_callback'), $text);

		// Next, handle inline images:  ![alt text](url "optional title")
		// Don't forget: encode * and _
		$text = preg_replace_callback('{
			(				                                  # wrap whole match in $1
			  !\[
				('.$this->getOption('nested_brackets_re').')		      # alt text = $2
			  \]
			  \s?			                                # One optional whitespace character
			  \(			                                # literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->getOption('nested_url_parenthesis_re').')	# src url = $4
				)
				[ \n]*
				(			                                  # $5
				  ([\'"])	                              # quote char = $6
				  (.*?)		                              # title = $7
				  \6		                                # matching quote
				  [ \n]*
				)?			                                # title is optional
			  \)
			)
			}xs',
			array(&$this, '_doImages_inline_callback'), $text);

		return $text;
	}

	/**
	 * @param array $matches A set of results of the `deImages` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doImages_reference_callback($matches) 
	{
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == '') {
			$link_id = strtolower($alt_text); // for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeAttribute($this->urls[$link_id]);
//			$result = '<img src="'.$url.'" alt="'.$alt_text.'"';
			$attrs = array( 'src'=>$url, 'alt'=>$alt_text );

			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
//				$result .= ' title="'.$title.'"';
				$attrs['title'] = $title;
			}
			if (isset($this->attributes[$link_id])) {
//				$result .= $this->doAttributes( $this->attributes[$link_id] );
				$attrs[] = $this->doAttributes( $this->attributes[$link_id] );
			}
//			$result .= $this->getOption('empty_element_suffix');
//			$result = $this->hashPart($result);

			$result = $this->hashPart(
				$this->runFormaterMethod('buildImage', $attrs)
			);

		}
		else {
			// If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}

	/**
	 * @param array $matches A set of results of the `doImages` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doImages_inline_callback($matches) 
	{
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
//		$result = '<img src="'.$url.'" alt="'.$alt_text.'"';
		$attrs = array( 'src'=>$url, 'alt'=>$alt_text );

		if (isset($title)) {
			$title = $this->encodeAttribute($title);
//			$result .=  ' title="'.$title.'"'; # $title already quoted
			$attrs['title'] = $title;
		}
//		$result .= $this->getOption('empty_element_suffix');
//		return $this->hashPart($result);

		return $this->hashPart(
			$this->runFormaterMethod('buildImage', $attrs)
		);
	}

// -----------------------------------
// LINKS DEFINITIONS
// -----------------------------------

	/**
	 * Strips link definitions from text, stores the URLs and titles in
	 * hash references.
	 *
	 * Link defs are in the form: ^[id]: url "optional title"
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _stripLinkDefinitions_callback()
	 * @todo Manage attributes (not working for now)
	 */
	public function stripLinkDefinitions($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		return preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>		# url = $2
							|
							  (\S+?)		# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)		# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	        # title is optional
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:				  # Attributes = $5
								(?<=\s)	  # lookbehind for whitespace
								(
									([ ]*\n)?
									((?:\S+?=\S+?)|(?:.+?=.+?)|(?:.+?=".*?")|(?:\S+?=".*?"))
								)
							  [ ]*
							)?	        # attributes are optional
							(\n+|\Z)
			}xm',
			array(&$this, '_stripLinkDefinitions_callback'), $text);
	}

	/**
	 * Add each link reference to `$urls` and `$titles` tables with index `$link_id`
	 *
	 * @param array $matches A set of results of the `stripLinkDefinitions()` function
	 * @return empty
	 */
	protected function _stripLinkDefinitions_callback($matches) 
	{
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		$this->attributes[$link_id] = $matches[5];
		return ''; // String that will replace the block
	}

// -----------------------------------
// LISTS
// -----------------------------------

	/**
	 * Retain current list level
	 */
	protected $list_level = 0;

	/**
	 * Form HTML ordered (numbered) and unordered (bulleted) lists.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doLists_callback()
	 */
	public function doLists($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = '(?:'.$marker_ul_re.'|'.$marker_ol_re.')';

		$markers_relist = array(
			$marker_ul_re => $marker_ol_re,
			$marker_ol_re => $marker_ul_re,
		);

		foreach ($markers_relist as $marker_re => $other_marker_re) {
			// Re-usable pattern to match any entirel ul or ol list:
			$whole_list_re = '
				(								              # $1 = whole list
				  (								            # $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			      # $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								            # $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						            # Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						            # Lookahead for another kind of list
					    \n
						\3						            # Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; // mx
			
			// We use a different prefix before nested lists than top-level lists.
			// See extended comment in _ProcessListItems().
			if ($this->list_level) {
				$text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
			else {
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
		}

		return $text;
	}

	/**
	 * @param array $matches A set of results of the `doLists` function
	 * @return string The text parsed
	 * @see processListItems()
	 * @see hashBlock()
	 */
	protected function _doLists_callback($matches) 
	{
		// Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = '(?:'.$marker_ul_re.'|'.$marker_ol_re.')';
		
		$list = $matches[1];
		$list_type = preg_match('/'.$marker_ul_re.'/', $matches[4]) ? 'ul' : 'ol';
		
		$marker_any_re = ( $list_type == 'ul' ? $marker_ul_re : $marker_ol_re );
		
		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);
		
//		$result = $this->hashBlock('<'.$list_type.'>'."\n" . $result . '</'.$list_type.'>');
//		return "\n". $result ."\n\n";

		$_method = $list_type=='ul' ? 'buildUnorderedList' : 'buildOrderedList';
		return "\n".$this->hashBlock(
			$this->runFormaterMethod($_method, "\n".$result)
		)."\n\n";
	}

	/**
	 *	Process the contents of a single ordered or unordered list, splitting it
	 *	into individual list items.
	 *
	 * The $this->list_level global keeps track of when we're inside a list.
	 * Each time we enter a list, we increment it; when we leave a list,
	 * we decrement. If it's zero, we're not in a list anymore.
	 *
	 * We do this because when we're not inside a list, we want to treat
	 * something like this:
	 *
	 *		I recommend upgrading to version
	 *		8. Oops, now this line is treated
	 *		as a sub-list.
	 *
	 * As a single paragraph, despite the fact that the second line starts
	 * with a digit-period-space sequence.
	 *
	 * Whereas when we're inside a list (or sub-list), that line will be
	 * treated as the start of a sub-list. What a kludge, huh? This is
	 * an aspect of Markdown's syntax that's hard to parse perfectly
	 * without resorting to mind-reading. Perhaps the solution is to
	 * change the syntax rules such that sub-lists must start with a
	 * starting cardinal number; e.g. "1." or "a.".
	 *
	 * @param str $list_str The list string to parse
	 * @param str $marker_any_re The marker we are processing
	 * @return string The list string parsed
	 * @see _processListItems_callback()
	 */
	public function processListItems($list_str, $marker_any_re) 
	{
		$this->list_level++;

		// trim trailing blank lines:
		$list_str = preg_replace('/\n{2,}\\z/', "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?							        # leading line = $1
			(^[ ]*)							      # leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	        # space only required if item is not empty
			)
			((?s:.*?))						    # list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm',
			array(&$this, '_processListItems_callback'), $list_str);

		$this->list_level--;
		return $list_str;
	}

	/**
	 * @param array $matches A set of results of the `processListItems()` function
	 * @return string The list string parsed
	 * @see runBlockGamut()
	 * @see runSpanGamut()
	 * @see doLists()
	 * @see doOutdent()
	 */
	protected function _processListItems_callback($matches) 
	{
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || 
			preg_match('/\n{2,}/', $item))
		{
			// Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runGamut( 'blockGamut', $this->doOutdent($item)."\n" );
		}
		else {
			// Recursion for sub-lists:
			$item = $this->doLists($this->doOutdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runGamut( 'spanGamut', $item );
		}

//		return '<li>' . $item . '</li>'."\n";
		return $this->runFormaterMethod('buildListItem', $item)."\n";
	}

// -----------------------------------
// META DATA
// -----------------------------------

	protected $metadata=array();
	
	protected static $inMetaData=0;
	
	// SPECIAL METADATA
	var $specials_metadata = array(
		'baseheaderlevel', 'quoteslanguage'
	);

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 */
	public function stripMetaData($text) 
	{
		$lines = preg_split('/\n/', $text);
		$text='';
		self::$inMetaData=1;
		foreach ($lines as $line) {
			if (self::$inMetaData===0) {
				$text .= $line."\n";
			} else {
				$text .= self::_stripMetaData($line);
				if (preg_match('/^$/', $line)) {
					self::$inMetaData = 0;
				}
			}
		}
		return $text;
	}

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 */
	public function _stripMetaData($line) 
	{
		$line = preg_replace_callback(
			'{^([a-zA-Z0-9][0-9a-zA-Z _-]*?):\s*(.*)$}i',
			array($this, '_callbackMetaData'), $line);

		if (strlen($line))
			$line = preg_replace_callback(
				'/^\s*(.+)$/', array($this, '_callbackMetaData_nextline'), $line);

		if (strlen($line)) $line .= "\n";
		return $line;
	}

	/**
	 * @param array $matches A set of results of the `transform` function
	 * @return string The text parsed
	 */
	protected function _callbackMetaData($matches) 
	{
		$meta_key = strtolower(str_replace(' ', '', $matches[1]));
		$this->metadata[$meta_key] = trim($matches[2]);
		return '';
	}

	/**
	 * @param array $matches A set of results of the `transform` function
	 * @return string The text parsed
	 */
	protected function _callbackMetaData_nextline($matches) 
	{
		$meta_key = array_search(end($this->metadata), $this->metadata );
		$this->metadata[$meta_key] .= ' '.trim($matches[1]);
		return '';
	}

	public function appendMetaData($text)
	{
		$metadata = $this->metadata;
//		$this->metadata=array();
		if (!empty($metadata)) {
			$metadata_str='';
			foreach($metadata as $meta_name=>$meta_value) {
				if (!empty($meta_name) && is_string($meta_name)) {
					if (in_array($meta_name, $this->specials_metadata))
						$this->specials_metadata[$meta_name] = $meta_value;
					else
						$metadata_str .= "\n".$this->doMetaData($meta_name.':'.$meta_value);
				}
			}
			$text = $metadata_str."\n\n".$text;
		}
		return $text;
	}

	/**
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 */
	public function doMetaData($text) 
	{
		return preg_replace_callback('{^([0-9a-zA-Z_-]*?):(.*)$}', 
			array($this, '_doMetaDataCallback'), $text);
	}

	protected function _doMetaDataCallback($matches)
	{
		return self::buildMetaDataString( $matches[1], $matches[2] );
		return $this->runFormaterMethod('buildMetaData', array(
			'name'=>$matches[1],
			'value'=>$matches[2]
		));
	}

	public function buildMetaDataString( $meta_name, $meta_value )
	{
		$special_mask = 'metadata_mask_'.strtolower($meta_name);
		$special_mask_opt = $this->getOption($special_mask);
		if (!empty($special_mask_opt)) {
			eval("\$_meta = sprintf('$special_mask_opt', \$meta_name, \$meta_value);");
			return $_meta;
		} else
			return sprintf($this->getOption('metadata_mask'), $meta_name, $meta_value);
	}

// -----------------------------------
// NOTES
// -----------------------------------

	/**
	 * Extra variables used during extra transformations.
	 */
	protected $footnotes = array();
	protected $glossaries = array();
	protected $citations = array();
	protected $notes_ordered = array();
	
	/**
	 * Remind all written notes and node_id for multi-references
	 */
	protected $written_notes = array();
		
	/**
	 * Give the current footnote, glossary or citation number.
	 */
	protected $footnote_counter = 1;
		
	/**
	 * Give the total parsed notes number.
	 */
	protected $notes_counter = 0;
		
	protected function _setupNotes() 
	{
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
		$this->footnote_counter = 1;
		$this->notes_counter = 0;
	}
	
	/**
	 * Clearing Extra-specific variables and run teardownGamuts
	 */
	protected function _teardownNotes() 
	{
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
	}

	/**
	 * Strips link definitions from text, stores the URLs and titles in hash references.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _stripFootnotes_callback()
	 */
	public function stripNotes($text) 
	{
		$this->written_notes = array();
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Link defs are in the form: [^id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					        # maybe *one* newline
			(						          # text = $2 (no blank lines allowed)
				(?:					
					.+				        # actual text
				|
					\n				        # newlines but 
					(?!\[\^.+?\]:\s)  # negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S) # ensure line is not blank and followed 
									          # by non-indented content
				)*
			)		
			}xm',
			array(&$this, '_stripNotes_callback'),
			$text);

		// Link defs are in the form: [#id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[(\#.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					        # maybe *one* newline
			(						          # text = $2 (no blank lines allowed)
				(?:					
					.+				        # actual text
				|
					\n				        # newlines but 
					(?!\[\^.+?\]:\s)  # negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S) # ensure line is not blank and followed 
									          # by non-indented content
				)*
			)		
			}xm',
			array(&$this, '_stripNotes_callback'),
			$text);

		return $text;
	}

	/**
	 * Build the footnote and strip it from content
	 *
	 * @param array $matches Results from the `stripFootnotes()` function
	 * @return string The text parsed
	 * @see doOutdent()
	 */
	protected function _stripNotes_callback($matches) 
	{
		if (0 != preg_match('/^(<p>)?glossary:/i', $matches[2])) {
			$this->glossaries[ $this->getOption('glossary_id_prefix') . $matches[1] ] = $this->doOutdent($matches[2]);
		} elseif (0 != preg_match('/^\#(.*)?/i', $matches[1])) {
			$this->citations[ $this->getOption('bibliography_id_prefix') . substr($matches[1],1) ] = $this->doOutdent($matches[2]);
		} else {
			$this->footnotes[ $this->getOption('footnote_id_prefix') . $matches[1] ] = $this->doOutdent($matches[2]);
		}
		return ''; // String that will replace the block
	}

	/**
	 * Replace footnote references in $text [^id] with a special text-token 
	 * which will be replaced by the actual footnote marker in appendFootnotes.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @todo Manage multi-calls of same note ID
	 */
	public function doNotes($text) 
	{
		if (!$this->in_anchor) {
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
//			$text = preg_replace('{\[\#(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
			$text = preg_replace('{\[(.+?)\]\[\#(.+?)\]}', " [\\1, F\x1Afn:\\2\x1A:]", $text);
		}
		return $text;
	}

	/**
	 * Append footnote list to text.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _appendFootnotes_callback()
	 * @see encodeAttribute()
	 * @see runBlockGamut()
	 */
	public function appendNotes($text) 
	{
		// First loop for references
		if (!empty($this->notes_ordered)) 
		{
			$tmp_notes_ordered = $this->notes_ordered;
			$_counter=0;
			while (!empty($tmp_notes_ordered)) 
			{
				$note_id = key($tmp_notes_ordered);
				unset($tmp_notes_ordered[$note_id]);
				if (!array_key_exists($note_id, $this->written_notes))
					$this->written_notes[$note_id] = $_counter++;
			}
		}
	
		$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
			array(&$this, '_appendNotes_callback'), $text);
	
		if (!empty($this->notes_ordered)) 
		{
//			$text .= "\n\n" . '<div class="footnotes">'."\n"
//				. '<hr'. $this->getOption('empty_element_suffix') ."\n" . '<ol>'."\n\n";
			$notes_ctt='';
			while (!empty($this->notes_ordered)) 
			{
				$note = reset($this->notes_ordered);
				$note_id = key($this->notes_ordered);
				unset($this->notes_ordered[$note_id]);

				// footnotes
				if (isset($this->footnotes[$note_id]))
//					$text .= self::_doFootnote( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'footnote' );

				// glossary
				elseif (isset($this->glossaries[$note_id]))
//					$text .= self::_doGlossary( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'glossary' );

				// citations
				elseif (isset($this->citations[$note_id]))
//					$text .= self::_doCitation( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'citation' );
			}

//			$text .= '</ol>'."\n" . '</div>';

			$text .= "\n\n".$this->runFormaterMethod(
				'buildDiv', 
				'<hr'.$this->getOption('empty_element_suffix')."\n".$this->runFormaterMethod('buildOrderedList', $notes_ctt)."\n", 
				array( 'class'=>'footnotes' )
			)."\n\n";
		}
		return $text;
	}

	/**
	 * Append footnote list to text.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _appendFootnotes_callback()
	 * @see encodeAttribute()
	 * @see runBlockGamut()
	 */
	protected function _doFootnote( $note_id, $type='footnote' ) 
	{
		$text='';
		
		switch($type)
		{
			case 'footnote': default:
				$type_attr = 'footnote';
				$class = $this->encodeAttribute( $this->getOption('footnote_link_class') );
				$title = $this->encodeAttribute( $this->getOption('footnote_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('footnote_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('footnote_backlink_title') );
				$note_str = $this->footnotes[$note_id];
				$list =& $this->footnotes;
				$back_ref_prefix = 'fnref';
				$ref_prefix = 'fn';
				break;
			case 'glossary':
				$type_attr = 'glossary';
				$class = $this->encodeAttribute( $this->getOption('glossary_link_class') );
				$title = $this->encodeAttribute( $this->getOption('glossary_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('glossary_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('glossary_backlink_title') );
				$note_str = substr( $this->glossaries[$note_id], strlen('glossary:') );				
				$list =& $this->glossaries;
				$back_ref_prefix = 'fngref';
				$ref_prefix = 'fng';
				break;
			case 'citation': 
				$type_attr = 'bibliography';
				$class = $this->encodeAttribute( $this->getOption('bibliography_link_class') );
				$title = $this->encodeAttribute( $this->getOption('bibliography_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('bibliography_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('bibliography_backlink_title') );
				$note_str = $this->citations[$note_id];
				$list =& $this->citations;
				$back_ref_prefix = 'fncref';
				$ref_prefix = 'fnc';
				break;
		}
		
		
		if (!empty($list[$note_id])) 
		{
//			$attr = ' rev="'.$type_attr.'"';
			$encoded_note_id = $this->encodeAttribute($note_id);
			$attrs = array(
				'href'=>'#'.$back_ref_prefix.':'.$encoded_note_id,
				'rev'=>$type_attr
			);
//			if ($backclass != '') $attr .= ' class="'.$backclass.'"';
//			if ($backtitle != '') $attr .= ' title="'.$backtitle.'"';
			if ($backclass != '') $attrs['class'] = $backclass;
			if ($backtitle != '') $attrs['title'] = $backtitle;
			
			if ($type=='glossary')
			{
				$note_str = preg_replace_callback('{
					^(.*?)				              # $1 = term
					\s*
					(?:\(([^\(\)]*)\)[^\n]*)?		# $2 = optional sort key
					\n{1,}
					(.*?)
					}x',
					array(&$this, '_doGlossary_callback'), $note_str);
			}
			elseif ($type=='citation')
			{
				$note_str = preg_replace_callback('{
					^\#(.*?)				              # $1 = term
					\s*
					(?:\(([^\(\)]*)\)[^\n]*)?		# $2 = optional sort key
					\n{1,}
					(.*?)
					}x',
					array(&$this, '_doCitation_callback'), $note_str);
			}
			
			$note_str .= "\n"; // Need to append newline before parsing.
			$note_str = $this->runGamut( 'blockGamut', $note_str."\n");				
			$note_str = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
					array(&$this, '_appendNotes_callback'), $note_str);
				
//			$attr = str_replace('%%', ++$this->notes_counter, $attr);
			foreach($attrs as $i=>$j)
			{
				$attrs[$i] = str_replace('%%', ++$this->notes_counter, $j);
			}

			$this->written_notes[$note_id] = $this->notes_counter;
				
			// Add backlink to last paragraph; create new paragraph if needed.
//			$backlink = '<a href="#'.$back_ref_prefix.':'.$note_id.'"'.$attr.'>&#8617;</a>';
			$backlink = $this->runFormaterMethod('buildLink', '&#8617;', $attrs);

			if (preg_match('{</p>$}', $note_str)) {
				$note_str = substr($note_str, 0, -4) . '&#160;'.$backlink.'</p>';
			} else {
//				$note_str .= "\n\n".'<p>'.$backlink.'</p>';
				$note_str .= "\n\n".$this->runFormaterMethod('buildParagraph', $backlink);
			}
				
//			$text = '<li id="'.$ref_prefix.':'.$note_id.'">'."\n" . $note_str . "\n" . '</li>'."\n\n";
			$text = $this->runFormaterMethod('buildListItem', "\n".$note_str."\n", array( 'id'=>$ref_prefix.':'.$encoded_note_id ))."\n\n";
		}
		return $text;
	}

	/**
	 * Build the glossary entry
	 *
	 * @param array $matches Results form the `appendGlossaries` function
	 * @return string The text parsed
	 */
	protected function _doGlossary_callback($matches)
	{
		return 
//			'<span class="glossary name">'.trim($matches[1]).'</span>'
			$this->runFormaterMethod('buildSpan', trim($matches[1]), array( 'class'=>'glossary name'))
//			.(isset($matches[3]) ? '<span class="glossary sort" style="display:none">'.$matches[2].'</span>' : '')
			.(isset($matches[3]) ? $this->runFormaterMethod('buildSpan', $matches[2], array(
				'class'=>'glossary sort',
				'style'=>'display:none'
			)) : '')
			."\n\n"
			.(isset($matches[3]) ? $matches[3] : $matches[2]);
	}

	/**
	 * Build the citation entry
	 *
	 * @param array $matches Results form the `appendGlossaries` function
	 * @return string The text parsed
	 */
	protected function _doCitation_callback($matches)
	{
		return 
//			'<span class="bibliography name">'.trim($matches[1]).'</span>'."\n\n".$matches[2];
			$this->runFormaterMethod('buildSpan', trim($matches[1]), array( 'class'=>'bibliography name'))
			."\n\n".$matches[2];
	}

	/**
	 * Append footnote and glossary list to text.
	 *
	 * @param array $matches Results form the `appendFootnotes()` or `appendGlossaries` functions
	 * @return string The text parsed
	 * @see encodeAttribute()
	 */
	protected function _appendNotes_callback($matches) 
	{
		// Create footnote marker only if it has a corresponding footnote *and*
		// the footnote hasn't been used by another marker.
		$footnote_node_id = $this->getOption('footnote_id_prefix') . $matches[1];
		$glossary_node_id = $this->getOption('glossary_id_prefix') . $matches[1];
		$citation_node_id = $this->getOption('bibliography_id_prefix') . $matches[1];

		$type=null;
		if (isset($this->footnotes[$footnote_node_id])) 
		{
			$type = 'footnote';
			$node_id = $footnote_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$node_id] = $this->footnotes[$node_id];
		}
		elseif (isset($this->glossaries[$glossary_node_id])) 
		{
			$type = 'glossary';
			$node_id = $glossary_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$glossary_node_id] = $this->glossaries[$glossary_node_id];
		}
		elseif (isset($this->citations[$citation_node_id])) 
		{
			$type = 'citation';
			$node_id = $citation_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$citation_node_id] = $this->citations[$citation_node_id];
		}

		if (!is_null($type))
		{
			switch($type)
			{
			case 'footnote': default:
				$type_attr = 'footnote';
				$class = $this->encodeAttribute( $this->getOption('footnote_link_class') );
				$title = $this->encodeAttribute( $this->getOption('footnote_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('footnote_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('footnote_backlink_title') );
				$note_str = $this->footnotes[$note_id];
				$list =& $this->footnotes;
				$back_ref_prefix = 'fnref';
				$ref_prefix = 'fn';
				break;
			case 'glossary':
				$type_attr = 'glossary';
				$class = $this->encodeAttribute( $this->getOption('glossary_link_class') );
				$title = $this->encodeAttribute( $this->getOption('glossary_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('glossary_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('glossary_backlink_title') );
				$note_str = substr( $this->glossaries[$note_id], strlen('glossary:') );				
				$list =& $this->glossaries;
				$back_ref_prefix = 'fngref';
				$ref_prefix = 'fng';
				break;
			case 'citation': 
				$type_attr = 'bibliography';
				$class = $this->encodeAttribute( $this->getOption('bibliography_link_class') );
				$title = $this->encodeAttribute( $this->getOption('bibliography_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('bibliography_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('bibliography_backlink_title') );
				$note_str = $this->citations[$note_id];
				$list =& $this->citations;
				$back_ref_prefix = 'fncref';
				$ref_prefix = 'fnc';
				break;
			}
			
			$num = array_key_exists($matches[1], $this->written_notes) ?
				$this->written_notes[$matches[1]] : $this->footnote_counter++;
			$encoded_node_id = $this->encodeAttribute($node_id);
//			$attr = ' rel="'.$type_attr.'"';
			$attrs = array(
				'href'=>'#'.$ref_prefix.':'.$encoded_node_id,
				'rel'=>$type_attr
			);
//			if ($class != '') $attr .= ' class="'.$class.'"';
//			if ($title != '') $attr .= ' title="'.$title.'"';
			if ($class != '') $attrs['class'] = $class;
			if ($title != '') $attrs['title'] = $title;

//			$attr = str_replace('%%', $num, $attr);
			foreach($attrs as $i=>$j)
			{
				$attrs[$i] = str_replace('%%', $num, $j);
			}
			
//			return
//				'<sup id="'.$back_ref_prefix.':'.$node_id.'">'.
//				'<a href="#'.$ref_prefix.':'.$node_id.'"'.$attr.'>'.$num.'</a>'.
//				'</sup>';
			return 
				$this->runFormaterMethod('buildSup', 
					$this->runFormaterMethod('buildLink', $num, $attrs), 
					array('id'=>$back_ref_prefix.':'.$node_id)
				);

		}

		return '[^'.$matches[1].']';
	}
		
// -----------------------------------
// PARAGRAPHS
// -----------------------------------

	/**
	 * Process paragraphs
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see runSpanGamut()
	 * @see unhash()
	 */
	public function formParagraphs($text) 
	{
		// Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		
		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		// Wrap <p> tags and unhashify HTML blocks
		foreach ($grafs as $key => $value) {
			$value = trim($this->runGamut( 'spanGamut', $value));
			
			// Check if this should be enclosed in a paragraph.
			// Clean tag hashes & block tag hashes are left alone.
			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);
			
			if ($is_p) {
//				$value = '<p>'.$value.'</p>';
				$value = $this->runFormaterMethod('buildParagraph', $value);

			}
			$grafs[$key] = $value;
		}
		
		// Join grafs in one text, then unhash HTML tags. 
		$text = implode("\n\n", $grafs);
		
		// Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);
		
		return $text;
	}
	
// -----------------------------------
// SPANS
// -----------------------------------

	/**
	 * Take the string $str and parse it into tokens, hashing embeded HTML,
	 * escaped characters and handling code spans.
	 *
	 * @param string $str The text to be parsed
	 * @return string The text parsed
	 * @see handleSpanToken()
	 */
	public function parseSpan($str) 
	{
		$output = '';
		
		$span_re = '{
				(
					\\\\'.$this->getOption('escape_chars_re').'
				|
					(?<![`\\\\])
					`+						          # code span marker
			'.( $this->getOption('no_markup') ? '' : '
				|
					<!--    .*?     -->		  # comment
				|
					<\?.*?\?> | <%.*?%>		  # processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs';

		while (1) {

			// Each loop iteration seach for either the next tag, the next 
			// openning code span marker, or the next escaped character. 
			// Each token is then passed to handleSpanToken.
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			// Create token from text preceding tag.
			if ($parts[0] != '') {
				$output .= $parts[0];
			}
			
			// Check if we reach the end.
			if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}
		
		return $output;
	}
	
	/**
	 * Handle $token provided by parseSpan by determining its nature and 
	 * returning the corresponding value that should replace it.
	 *
	 * @param string $token The token string to use
	 * @param string $str The text to be parsed (by reference)
	 * @return string The text parsed
	 * @see hashPart()
	 */
	public function handleSpanToken($token, &$str) 
	{
		switch ($token{0}) {
			case '\\':
				return $this->hashPart('&#'. ord($token{1}). ';');
			case '`':
				// Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', 
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; // return as text since no ending marker found.
			default:
				return $this->hashPart($token);
		}
	}

// -----------------------------------
// TABLES
// -----------------------------------

	/**
	 * Form HTML tables.
	 * 
	 * Find tables with leading pipe:
	 * 
	 *    | Header 1 | Header 2
	 *    | -------- | --------
	 *    | Cell 1   | Cell 2
	 *    | Cell 3   | Cell 4
	 * 
	 * Or without:
	 * 
	 *    Header 1 | Header 2
	 *    -------- | --------
	 *    Cell 1   | Cell 2
	 *    Cell 3   | Cell 4
	 * 
	 * @param string $text Text to parse
	 * @return string Text with table parsed
	 * @see _doTable_leadingPipe_callback()
	 * @see _DoTable_callback()
	 *
	 * @todo Manage tables with headers at the end (cf. MultiMD)
	 * @todo Manage separated body of a table (cf. MultiMD)
	 * @todo What is "colgroup" ? (cf. MultiMD)
	 */
	public function doTables($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Find tables with leading pipe.
		$text = preg_replace_callback('
			{
				^							                # Start of a line
				(                             # A caption between brackets (optional)
					[ ]{0,'.$less_than_tab.'}
					\[.*?\][ \t]*\n
				)?
				[ ]{0,'.$less_than_tab.'}	    # Allowed whitespace.
				(
					(?>
						[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
						[|]							          # Optional leading pipe (present)
						.* [|] .* \n
					)*
				) 				                    # $1: Header rows (at least one pipe)

				[ ]{0,'.$less_than_tab.'}	    # Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	    # $2: Header underline
				
				(       							        # $3: Cells
					(?>
						[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
						[|] .* \n                 # Row content
					)*
				)
				(?=\n|\Z)					            # Stop at final double newline.
			}xm',
			array(&$this, '_DoTable_callback'), $text);
		
		// Find tables without leading pipe.
		$text = preg_replace_callback('
			{
				^							                # Start of a line
				(                             # A caption between brackets (optional)
					[ ]{0,'.$less_than_tab.'}
					\[.*?\][ \t]*\n
				)?
				[ ]{0,'.$less_than_tab.'}	    # Allowed whitespace.
				(
					(?>
						[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
						\S .* [|] .* \n
					)*
				) 				                    # $1: Header rows (at least one pipe)
				
				^[ ]{0,'.$less_than_tab.'}	  # Allowed whitespace at the beginning
				([-:]+[ ]*[|][-| :]*) \n	    # $2: Header underline
				
				(       							        # $3: Cells
					(?>
						[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
						 .* [|] .* \n		          # Row content
					)*
				)
				(?=\n|\Z)					            # Stop at final double newline.
			}xm',
			array(&$this, '_DoTable_callback'), $text);

		return $text;
	}

	/**
	 * Form HTML tables: removes leading pipe for each row
	 * 
	 * @param array $matches Results from the `doTables()` function
	 * @return function Pass its result to the `_doTable_callback()` function
	 * @see doTable()
	 * @see _DoTable_callback()
	 */
	protected function _doTable_leadingPipe_callback($matches) 
	{
		$head		    = $matches[1];
		$underline		= $matches[2];
		$content	  	= $matches[3];
		$content	  	= preg_replace('/^ *[|]/m', '', $content);
		return $this->_doTable_callback(array($matches[0], $head, $underline, $content));
	}

	/**
	 * Form HTML tables: parses table contents
	 * 
	 * @param array $matches Results from the `doTables()` function
	 * @return function Pass its result to the `hashBlock()` function
	 * @see doTable()
	 * @see hashBlock()
	 * @see runSpanGamut()
	 * @see parseSpan()
	 */
	protected function _doTable_callback($matches) 
	{
//self::doDebug('',$matches);
		// The head string may have a begin slash
		$caption    = count($matches)>3 ? $matches[1] : null;
		$head		= count($matches)>3 ? preg_replace('/^ *[|]/m', '', $matches[2]) : preg_replace('/^ *[|]/m', '', $matches[1]);
		$underline	= count($matches)>3 ? $matches[3] : $matches[2];
		$content	= count($matches)>3 ? preg_replace('/^ *[|]/m', '', $matches[4]) : preg_replace('/^ *[|]/m', '', $matches[3]);

		// Remove any tailing pipes for each line.
		$underline	= preg_replace('/[|] *$/m', '', $underline);
		$content	= preg_replace('/[|] *$/m', '', $content);
		
		// Reading alignement from header underline.
		$separators	= preg_split('/ *[|] */', $underline);
		foreach ($separators as $n => $s) {
			if (preg_match('/^ *-+: *$/', $s))
				$attr[$n] = ' style="text-align:right;"';
			else if (preg_match('/^ *:-+: *$/', $s))
				$attr[$n] = ' style="text-align:center;"';
			else if (preg_match('/^ *:-+ *$/', $s))
				$attr[$n] = ' style="text-align:left;"';
			else
				$attr[$n] = '';
		}
		
		// Split content by row.
		$headers = explode("\n", trim($head, "\n"));

		$text = '<table>'."\n";
		if (!empty($caption)) {
			$table_id = $this->header2Label( $caption );
			$text .= preg_replace('/\[(.*)\]/', '<caption id="'.$table_id.'">$1</caption>'."\n", 
				$this->runGamut( 'spanGamut', $caption) );
		}

		$text .= '<thead>'."\n";
		foreach ($headers as $_header) {
			// Parsing span elements, including code spans, character escapes, 
			// and inline HTML tags, so that pipes inside those gets ignored.
			$_header		= $this->parseSpan($_header);

			// Split row by cell.
			$_header		= preg_replace('/[|] *$/m', '', $_header);
			$_headers	  = preg_split('/[|]/', $_header);
			$col_count	= count($_headers);

			// Write column headers.
			$text .= '<tr>'."\n";
			// we first loop for colspans
			$headspans = array();
			foreach ($_headers as $_i => $_cell) {
				if ($_cell=='') {
					if ($_i==0) $headspans[1]=2;
					else {
						if (isset($headspans[$_i-1])) $headspans[$_i-1]++;
						else $headspans[$_i-1]=2;
					}
				}
			}
			foreach ($_headers as $n => $__header) {
				if ($__header!='')
					$text .= '  <th'.(isset($headspans[$n]) ? ' colspan="'.$headspans[$n].'"' : '')
						.$attr[$n].'>'.$this->runGamut( 'spanGamut', trim($__header)).'</th>'."\n";
			}
			$text .= '</tr>'."\n";
		}
		$text .= '</thead>'."\n";
		
		// Split content by row.
		$rows = explode("\n", trim($content, "\n"));
		
		$text .= '<tbody>'."\n";
		foreach ($rows as $row) {
			// Parsing span elements, including code spans, character escapes, 
			// and inline HTML tags, so that pipes inside those gets ignored.
			$row = $this->parseSpan($row);
			
			// Split row by cell.
			$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');
			
			$text .= '<tr>'."\n";
			// we first loop for colspans
			$colspans = array();
			foreach ($row_cells as $_i => $_cell) {
				if ($_cell=='') {
					if ($_i==0) $colspans[1]=2;
					else {
						if (isset($colspans[$_i-1])) $colspans[$_i-1]++;
						else $colspans[$_i-1]=2;
					}
				}
			}
			foreach ($row_cells as $n => $cell) {
				if ($cell!='')
					$text .= '  <td'.(isset($colspans[$n]) ? ' colspan="'.$colspans[$n].'"' : '')
						.$attr[$n].'>'.$this->runGamut( 'spanGamut', trim($cell)).'</td>'."\n";
			}
			$text .= '</tr>'."\n";
		}
		$text .= '</tbody>'."\n";
		$text .= '</table>';
		
		return $this->hashBlock($text) . "\n";
	}

// -----------------------------------
// UTILITIES
// -----------------------------------

	/**
	 * Remove UTF-8 BOM and marker character in input, if present.
	 */
	public function doRemoveUtf8Marker($text) 
	{
		return preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);
	}

	/**
	 * Standardize line endings: DOS to Unix and Mac to Unix
	 */
	public function doStandardizeLineEnding($text) 
	{
		return preg_replace('{\r\n?}', "\n", $text);
	}

	/**
	 * Make sure $text ends with a couple of newlines:
	 */
	public function doAppendEndingNewLines($text) 
	{
		return $text."\n\n";
	}

	/**
	 * Remove one level of line-leading tabs or spaces
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 */
	public function doOutdent($text) 
	{
		return preg_replace('/^(\t|[ ]{1,'.$this->getOption('tab_width').'})/m', '', $text);
	}

	/**
	 * Strip any lines consisting only of spaces and tabs.
	 * This makes subsequent regexen easier to write, because we can
	 * match consecutive blank lines with /\n+/ instead of something
	 * contorted like /[ ]*\n+/ .
	 */
	public function stripSapcedLines($text) 
	{
		return preg_replace('/^[ ]+$/m', '', $text);
	}

	/**
	 * Rebuild attributes string 'a="b"'.
	 *
	 * @param string $attributes The attributes to parse
	 * @return string The attributes processed
	 */
	public function doAttributes($attributes)
	{
		return preg_replace('{
			(\S+)=
			(["\']?)                  # $2: simple or double quote or nothing
			(?:
				([^"|\']\S+|.*?[^"|\']) # anything but quotes
			)
			\\2                       # rematch $2
			}xsi', ' $1="$3"', $attributes);
	}

	/**
	 * Encode text for a double-quoted HTML attribute. This function
	 * is *not* suitable for attributes enclosed in single quotes.
	 *
	 * @param string $text The attributes content
	 * @return string The attributes content processed
	 */
	public function encodeAttribute($text) 
	{
		$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}
	
	/**
	 * Smart processing for ampersands and angle brackets that need to 
	 * be encoded. Valid character entities are left alone unless the
	 * no-entities mode is set.
	 *
	 * @param string $text The text to encode
	 * @return string The encoded text
	 */
	public function encodeAmpsAndAngles($text) 
	{
		if (true===$this->getOption('no_entities')) {
			$text = str_replace('&', '&amp;', $text);
		} else {
			// Ampersand-encoding based entirely on Nat Irons's Amputator
			// MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);
		}
		// Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}

	public function header2Label($text) 
	{
  	// strip all Markdown characters
	  	$text = str_replace( 
  			array("'", '"', '?', '*', '`', '[', ']', '(', ')', '{', '}', '+', '-', '.', '!', "\n", "\r", "\t"), 
  			'', strtolower($text)
  		);
	  	// strip the rest for visual signification
  		$text = str_replace( array('#', ' ', '__', '/', '\\'), '_', $text );
		// strip non-ascii characters
		return preg_replace("/[^\x9\xA\xD\x20-\x7F]/", '', $text);
	}


}

// Endfile