Title:   A Sample MultiMarkdown Document
Author:  Pierre Cassat
Date:    February 20, 2012
Comment: This is a comment intended to demonstrate
         metadata that spans multiple lines, yet
         is treated as a single value.
Test:    And this is a new key-value pair
Base Header Level:  2
Quotes Language: french

# Basic tags from the original Markdown

## Blocks and structural elements

Titles: `#my title level 1` or `###my title level 3` (example: just above)

Paragraphs: just pass a line (exemple this line ...)

Pre-formatted: begin lines with 4 spaces (example this block)

        pre formed content

Blockquotes and citations: begin lines by '>'

    > my citation

Example:

> My citation
>
> With a paragraph and some `code`
>
>     and even a preformatted string

An horizontal rule: 3 or more hyphens, asterisks or underscores on a line

    ----

Example: 

----


## Typography

Bold text: `**bolded content**` or `__bolded content__` (example: **bold text**)

Italic text: `*italic content*` or `_italic content_` (example: *italic text*)

A code span: `` `function()` `` (example: `function()`)


## Links and images

Automatic links: `<http://example.com/>` and `<addres@email.com>` (example: <http://example.com/> and <addres@email.com>)

An hypertext link: `[link text](http://example.com/ "Optional link title")` (example: [link text](http://example.com/ "Optional link title"))

A referenced hypertext link: `[link text] [myid]` and after the paragraph, anywhere in the document `[myid]: http://example.com/ "Optional link title"` (example: [link text] [myid])

An embedded image: `![Alt text](http://test.com/data1/images/1.jpg "Optional image title")` (example: ![Alt text](http://test.com/data1/images/1.jpg "Optional image title"))

A referrenced embedded image: `![Alt text][myimageid]` and after the paragraph, anywhere in the document `[myimageid]: http://test.com/data1/images/1.jpg "Optional image title"` (example: ![iumage] [myimageid])

[myid]: http://example.com/ "Optional link title"
[myimageid]: http://test.com/data1/images/1.jpg "Optional image title"
[atest]: http://myexample.com/ (Optional link title)


## HTML

A list: begin each entry by an asterisk, a plus or an hyphen followed by 3 spaces

    -   first item
    *   second item

Example:

-   first item
-   second item

An ordered list: begin each entry by a number followed by a dot and 3 spaces

    1.   first item
    1.   second item

Example:

1.   first item
1.   second item


# Advanced tags from the Markdown Extra feature

## Blocks and structural elements

Fenced code block: a line of tildes (at least 3)

    ~~~~
    My code here
    ~~~~

Example:
~~~~
My code here
~~~~


## Typography

Allowing underscores in emphasis: `__my_undersoced_bold_word__` (example: __my_undersoced_bold_word__)


## Links and images

An inpage link: `[link text](#anchor)` will return to `# my title {#anchor}` (example: [link text](#myanchor))


## HTML

A table:

    | First Header  | Second Header |
    | ------------- | ------------: |
    | Content Cell  | Content Cell  |
    | Content Cell  | Content Cell  |

or (without leading pipe) :

    First Header  | Second Header |
    ------------- | ------------: |
    Content Cell  | Content Cell  |
    Content Cell  | Content Cell  |

or (not constant spaces) :

    | First Header | Second Header |
    | ------------ | ------------: |
    | Cell | Cell |
    | Cell | Cell |

Example:

| First Header  | Second Header |
| ------------- | ------------: |
| Content Cell  | Content Cell  |
| Content Cell  | Content Cell  |

and

First Header  | Second Header |
------------- | ------------: |
Content Cell  | Content Cell  |
Content Cell  | Content Cell  |

and

| First Header | Second Header |
| ------------ | ------------: |
| Cell | Cell |
| Cell | Cell |


A definition:

    Apple
    :   Pomaceous fruit of plants of the genus Malus in 
        the family Rosaceae.

Example:

Term 1
:   This is a definition with two paragraphs. Lorem ipsum 
    dolor sit amet, consectetuer adipiscing elit. Aliquam 
    hendrerit mi posuere lectus.

    Vestibulum enim wisi, viverra nec, fringilla in, laoreet
    vitae, risus.

:   Second definition for term 1, also wrapped in a paragraph
    because of the blank line preceding it.

Term 2
:   This definition has a code block, a blockquote and a list.

        code block.

    > block quote
    > on two lines.

    1.  first list item
    2.  second list item

A footnote:

    That's some text with a footnote.[^1]

    [^1]: And that's the footnote.

Example: That's some[^2] text with a footnote.[^1][^3]

[^1]: And that's the footnote.

    That's the second paragraph.

[^2]: And that's another footnote
	on *two lines* to test ...

[^3]: And that's a footnote [with a link](http://example.com).

An abbreviation:

    *[HTML]: Hyper Text Markup Language

Example: A text whit HTML expression.

*[HTML]: Hyper Text Markup Language

*[W3C]:  World Wide Web Consortium


----

#### Anchor for tests with a specific anchor `{#myanchor}` ... {#myanchor}


# Advanced tags from the Multi Markdown feature

## Blocks and structural elements

Citations : like footnote begining by a sharp

    This is a statement that should be attributed to
    its source[p. 23][#Doe:2006].

    And following is the description of the reference to be
    used in the bibliography.

    [#Doe:2006]: John Doe. *Some Big Fancy Book*.  Vanity Press, 2006.

Example:

This is a statement that should be attributed to
its source[p. 23][#Doe:2006].

And following is the description of the reference to be
used in the bibliography.

[#Doe:2006]: John Doe. *Some Big Fancy Book*.  Vanity Press, 2006.

Glossary footnotes :

    [^glossaryfootnote]: glossary: term (optional sort key)
        The actual definition belongs on a new line, and can continue on
        just as other footnotes.

Example:

My text with a footnote ref [^glossaryfootnote].

[^glossaryfootnote]: glossary: term (2)
	The actual definition belongs on a new line, and can continue on
	just as other footnotes.


## Typography

Nothing new ...


## Links and images

An self-reference link: `[link text][anchor]` will refer to `My text[anchor]` (example: [link text][#mynewanchor] or [atitleanchor][])

A referenced link image with attributes: `[mylink][]` will refer to reference `[mylink]: http://test.com/ "Optional title" class=external style="border: solid black 1px;"`

Example: [mylink][] and [mylink2][] and [mylink3][]

An embedded image with attributes: `![myimage][]` will refer to reference `[myimage]: http://test.com/data1/images/1.jpg "Optional image title" width=40px height=40px`

Example: ![myimage][]

A referenced embedded image with attributes: `![alternative text][myimage]` will refer to reference `[myimage]: http://test.com/data1/images/1.jpg "Optional image title" width=40px height=40px`

Example: ![my image][myimage]

[myimage]: http://test.com/data1/images/1.jpg "Optional image title" width=40px height=40px
[mylink]: http://test.com/ "Optional title" class=external rel=external
[mylink2]: http://test.com/ "Optional title" class=external rel=external style="border: solid black 1px;"
[mylink3]: http://test.com/ "Optional title" class=external rel="external" 
	style="border: solid black 1px;"

## HTML

A table:

    |             | Grouping                    ||
    First Header  | Second Header | Third header |
    ------------- | ------------: | :----------: |
    Content Cell  |  *Long Cell*                ||
    Content Cell  | **Cell**      | **Cell**     |
    
    New section   |   More        |         Data |
    And more      |           And more          ||
    [prototype table]

Example:

|             | Grouping                    ||
First Header  | Second Header | Third header |
------------- | ------------: | :----------: |
Content Cell  |  *Long Cell*                ||
Content Cell  | **Cell**      | **Cell**     |

New section   |   More        |         Data |
And more      |           And more          ||
[prototype table]

New example:

[prototype *table*]
|             | Grouping                    ||
First Header  | Second Header | Third header |
First comment  | Second comment | Third comment |
------------- | ------------: | :----------: |
Content Cell  |  *Long Cell*                ||
Content Cell  | **Cell**      | **Cell**     |
New section   |   More        |         Data |
And more      |           And more          ||
And more                     || And more     |


----

#### Anchor for tests ... [atitleanchor]

This paragrpah contains a `[mynewanchor]` info [mynewanchor], so it can be referenced ...


# Some inline HTML for tests

This is a regular paragraph.

<table border="2px" cellspacing="2px" cellpadding="6px">
    <tr>
        <td>Foo</td>
        <td>*Foo*</td>
        <td>`Foo`</td>
    </tr>
</table>

This is another regular paragraph, with another call of footnote 2 [^2].

Below is the same table as above with argument `markdown="1"`.

<table border="2px" cellspacing="2px" cellpadding="6px" markdown="1">
<tr>
	<td>Foo</td>
	<td>*Foo*</td>
	<td>`Foo`</td>
</tr>
</table>

This is another regular paragraph.
