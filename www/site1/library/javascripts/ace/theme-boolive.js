ace.define("ace/theme/boolive", ["require", "exports", "module", "ace/lib/dom"], function (e, t, n) {
    t.isDark = !1, t.cssClass = "ace-boolive", t.cssText = '' +




        '.ace-boolive .ace_gutter {background: transparent; color: #ccc; border-right: 1px dotted #ccc;}' +
        '.ace-boolive  {background: #fff;color: #000;}' +
        '.ace-boolive .ace_tag {color:#0000FF;}' +
        '.ace-boolive .ace_attribute-name {color:#800000;}' +

        '.ace-boolive .ace_keyword {color: #252FFF; font-weight: bold;}' +
        '.ace-boolive .ace_keyword.ace_operator {color:#000; font-weight: normal;}' +
        '.ace-boolive .ace_string {color: #008100;}' +
        '.ace-boolive .ace_support {color: #000; font-weight: bold;}' +
        '.ace-boolive .ace_storage.ace_type {color: #252FFF; font-weight: bold;}' +
        '.ace-boolive .ace_php_tag {color:#f00; font-weight:bold}' +
        '.ace-boolive .ace_variable {color: #660000;}' +
        '.ace-boolive .ace_comment {color: #999;}' +
        '.ace-boolive .ace_comment.ace_doc.ace_tag{color: #777; font-weight: bold;}' +
        '.ace-boolive .ace_variable.ace_class {color: teal;}' +
        '.ace-boolive .ace_constant.ace_numeric {color: #F00;}' +
        '.ace-boolive .ace_constant.ace_buildin {color: #0086B3;}' +
        '.ace-boolive .ace_entity {color: #660E7A;}' +


//        '.ace-boolive .ace_variable.ace_language  {color: #0086B3;}' +
//        '.ace-boolive .ace_paren {font-weight: bold;}' +
        '.ace-boolive .ace_boolean {color: #252FFF;}' +
        '.ace-boolive .ace_string.ace_regexp {color: #009926;font-weight: normal;}' +
//        '.ace-boolive .ace_variable.ace_instance {color: teal;}' +
        '.ace-boolive .ace_constant.ace_language {color: #252FFF;}' +
        '.ace-boolive .ace_cursor {color: black;}' +
        '.ace-boolive .ace_marker-layer .ace_active-line {background: transparnt;/*#FFFFCC;*/}' +
        '.ace-boolive .ace_marker-layer .ace_selection {background: #B0C5E3;}' +
        '/* bold keywords cause cursor issues for some fonts */' +
        '/* this disables bold style for editor and keeps for static highlighter */' +
        '.ace-boolive.ace_nobold .ace_line > span {font-weight: normal !important;}' +
        '.ace-boolive .ace_marker-layer .ace_step {background: rgb(252, 255, 0);}' +
        '.ace-boolive .ace_marker-layer .ace_stack {background: rgb(164, 229, 101);}' +
        '.ace-boolive .ace_marker-layer .ace_bracket {margin: 0;border: 0; background: #9AD7FF;}' +
        '.ace-boolive .ace_gutter-active-line {background-color: transparent;}' +
        '.ace-boolive .ace_marker-layer .ace_selected-word {background: rgb(250, 250, 255);border: 1px solid rgb(200, 200, 250);}' +
        '.ace-boolive .ace_print-margin {width: 1px;background: #e8e8e8;}' +
        '.ace-boolive .ace_indent-guide {background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAE0lEQVQImWP4////f4bLly//BwAmVgd1/w11/gAAAABJRU5ErkJggg==") right repeat-y;}';
    var r = e("../lib/dom");
    r.importCssString(t.cssText, t.cssClass)
})