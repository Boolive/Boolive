Boolive Change Log
==================

Version 2.0 preview work in progress
------------------------------------
- enh #1: Remove engine\config.classes.php, update classes autoloading (AzatGaliev)
- enh #3: Changed the style code in all files (VladimirShestakov)
- enh #39: Moved all the directories in the root of the Site. Created a class Boolive instead Engine and Classes. The core file is index.php  (VladimirShestakov)
- enh #42: Add child res for base widget /Library/basic/widgets/Widget
- enh: Create a package "contents_widgets" and "content_samples". Existing objects are moved into them. (VladimirShestakov)
- enh: Numbering nesting level starts at zero. A root object level = 0 (VladimirShestakov)
- bug: Updated algorithm of working embedded values ​​in the class \Boolive\values\Values. Removed references to property _value (VladimirShestakov)
- bug: Fix some paths in code for compatibility with UNIX (AzatGaliev)
- new #4: Add CHANGELOG.md file (AzatGaliev)
- new #10: Update all objects in "library/basic" with added contents, members and simple objects (VladimirShestakov)
- new #14: Added layout widget "boolive" in the "library/layouts" and "interfaces/html/body" (VladimirShestakov)
- new #15: Added widget logo to display an logo (AzatGaliev)
- new #16: Added widget ViewObject to display an object (VladimirShestakov)
- new #17: Added widget ViewObjectsList to display list of objects (VladimirShestakov)
- new #18: Added widget Content to the boolive layout (VladimirShestakov)
- new #19: Added widget Menu in the basic package (VladimirShestakov)
- new #20: Added widget for view Page (VladimirShestakov)
- new #21: Added widget for view RichText (VladimirShestakov)
- new #22: Added widget for view Head of text (VladimirShestakov)
- new #23: Added widget for view Paragraph of text (VladimirShestakov)
- new #24: Added widget for view Image in text (VladimirShestakov)
- new #32: Added widget for view Part (VladimirShestakov)
- new #33: Added widget Focuser (VladimirShestakov)
- new #34: Added widget PagePreview with style (VladimirShestakov)
- new #35: Added widget PageNavigation in the basic package and layout boolive for widget the part (VladimirShestakov)
- new #43: Added handler forms in the package "basic" and in /Interfaces (VladimirShestakov)
- new #44: Added indication that the object exists (VladimirShestakov)
- new: Added object the Option for automatic views (VladimirShestakov)
- new: Added top menu and sidebar menu in the boolive layout (VladimirShestakov)