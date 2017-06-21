# On HTML formatting

It's almost universally accepted that XML code should be formatted "ladder-style": every element is indented one level relative to its parent:

	<a>
		<b>
			text, maybe
			<foo />
		</b>
	</a>

But with HTML there is inconsistency.

HTML has a troubled and complicated life. Born in 1990-s, it was abused as a child, and now is [brutally molested](http://w3c.github.io/html/) by WHATWG. Started, presumably, as a tool for writing academic texts, it provided means to write an article with some semantic markers and, most importantly, links to other articles. There were no nested block elements. The only block element was, probably, "P". There was no need for fancy indentation in the source code (and it was, probably, undesirable, as the source back then was almost as readable as first versions of Markdown).

There were no tables in first versions of HTML. Probably, such data was meant to be displayed in the "PRE" tag. But the TABLE element got introduced, with all its child elements, and formatting HTML got a little funky. You still can format a table kinda elegantly, omitting closing tags for TD, TH and TR {it's valid, if you didn't know}, but often tables are too big and are easier to format in the ladder style.

Then LAYER/DIV element got introduced. Probably around the same time webmasters were already pumping out pages using nested tables to create custom designs with columns, sidebars and other things that can't be done nicely in HTML even today (because it's a hypertext formatting language, not a layout specification language). That deep nesting made the ladder style necessary to cope with the source code.

After server-side programming/scripting got ubiquitous, most HTML became generated and the question of its formatting became moot, except for those who still used HTML for its original purpose: writing articles. But now we are dealing with more things in that area of application: HEADER, FOOTER, ARTICLE, SECTION, NAV, ASIDE... Many elements to choose from, and then to decide again: to indent or not indent?

Section and article elements were designed to be nested, so that points to the ladder style. But what about the PRE element? You can't nest a PRE element, because its own indentation will affect the rendering. And what about documents with many multilevel headings? HTML5 kinda suggested using nested SECTION elements with H1 headings each. Ladder style in that case produced inconvenient code, because the nested contents were big paragraphs, so most of the screen was a big multilevel space at the left with text spilling over the right edge of the screen.

For some reason, the WHATWG of some other gangs behind the scene didn't agree on something, and promised support for nested sections didn't happen. Validators became marking nested section-h1-section-h1 constructrs as invalid. Now we are back at the original h1-h6 model, with section and article elements now useless. So one reason less for ladder indentation in articles. Note that it doesn't concern typical web pages where nested layout is still a must, and even section or article elements may be used for some imaginary semantic purposes.
