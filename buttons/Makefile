build:
	recess --compile buttons.less > buttons.css
	recess --compile --compress buttons.less > buttons.min.css 

	recess --compile style.less > style.css
	cat buttons.css >> style.css
	cat prism.css >> style.css

	recess --compress style.css > style.min.css 

	uglifyjs prism.js > prism.min.js
	uglifyjs script.js prism.js > script.min.js
