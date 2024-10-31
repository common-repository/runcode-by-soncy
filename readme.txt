=== runcode by soncy ===
Contributors: soncy 
Tags: code,runcode
Requires at least: 2.0.2
Tested up to: 2.8 beta2
Stable tag: 1.1.5
	
A plugin for WordPress, Run html,css,javascript code in a textarea,and you can control the size of textarea.

== Description ==

A plugin for WordPress, Run html,css,javascript code in a textarea,and you can control the size of textarea.
	
== Installation ==

1. Extract and upload `runcode-by-soncy` to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Set default width and default fontsize.
== Frequently Asked Questions ==

= how to use? =  

you can use like this 

`<runcode>
         <script>alert('soncy');</script>
</runcode>`
or  

`<runcode width="600" height="100" size="12">
         <script>alert('soncy');</script>
</runcode>`

or

`[runcode width="600" height="100" size="12"]
         <script>alert('soncy');</script>
[/runcode]`

== Screenshots ==

1. Watch a demo here:  
<http://www.eyike.com/html/y2009/wordpress-runcode-soncy.html>.

2.Screenshots

`/tags/1.1.5/screenshot-1.png`  
`/tags/1.1.5/screenshot-2.png`

== Change Log ==

v1.0 Add multi-language support. Add new feature: copy to clipboard.  
v1.1 Add new feature: control the size of textarea.  
v1.1.2 Add some Language File,change "Default Size" to "Default Font Size".  
v1.1.3 Add new feature: save as... Fixed a bug.  
v1.1.4 Fixed an important bug.  
v1.1.5 Add new feature: support "[runcode][/runcode]" tags.recommended this tag to use.