<?php
/**
 */

##@emd@## CONFIG ##@emd@##

blockGamut[doLists]=40

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The unordered list content
	 * @param array $attrs The unordered list attributes if so
	 * @return string The unordered list tag string
	 */
	public static function buildUnorderedList( $text, $attrs=array() );

	/**
	 * @param string $text The ordered list content
	 * @param array $attrs The ordered list attributes if so
	 * @return string The ordered list tag string
	 */
	public static function buildOrderedList( $text, $attrs=array() );

	/**
	 * @param string $text The list item content
	 * @param array $attrs The list item attributes if so
	 * @return string The list item tag string
	 */
	public static function buildListItem( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

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

##@emd@## !GRAMMAR ##@emd@##

// Endfile