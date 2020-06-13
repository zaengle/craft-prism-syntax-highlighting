/**
 * Prism Syntax Highlighting plugin for Craft CMS
 *
 * Prism Syntax Highlighting JS
 *
 * @author    Josh Smith <me@joshsmith.dev>
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 * @link      https://www.joshsmith.dev
 * @package   PrismSyntaxHighlighting
 * @since     1.1.0
 */
;(function($){

    $.fn.PrismSyntaxHighlightingField = function(settings){
        var $el = $(this).find('code'),
            $wrapper = $(this),
            $editor = $wrapper.find('.js--prism-syntax-highlighting'),
            $textarea = $wrapper.find('.js--prism-syntax-highlighting-textarea'),
            $editorLanguage = $wrapper.find('.js--prism-syntax-highlighting-editor-language select'),
            $editorTheme = $wrapper.find('.js--prism-syntax-highlighting-editor-theme select');

        var element = $el.get(0);
        var editor = bililiteRange.fancyText(element, Prism.highlightElement);

        // init the undo/redo
        bililiteRange(editor).undo(0).data().autoindent = true;

        // Handle formatting shortcuts
        $el.on('keydown', function(e){
            switch(e.keyCode){

                // Tab
                case 9:
                    e.preventDefault();
                    $el.sendkeys('\t');
                    break;

                // {
                case 219:
                    if( (e.ctrlKey || e.metaKey) ){
                        e.preventDefault();
                        bililiteRange(element).bounds('selection').unindent();
                    }
                    break;

                // }
                case 221:
                    if( (e.ctrlKey || e.metaKey) ){
                        e.preventDefault();
                        bililiteRange(element).bounds('selection').indent('\t');
                    }
                    break;
            }

            // control/cmd z
            if ((e.ctrlKey || e.metaKey) && e.which == 90) {
                e.preventDefault(); bililiteRange.undo(e);
            }
            // control/cmd y
            if ((e.ctrlKey || e.metaKey) && e.which == 89){
                e.preventDefault(); bililiteRange.redo(e);
            }
        }).on('keyup', function(e){
            $textarea.val(bililiteRange(element).text());
        }).trigger('keyup');

        // Handle change to language
        $editorLanguage.on('change', function(e){
            replaceLanguage([$wrapper, $el], $(this).val());
            Prism.highlightAllUnder($wrapper.get(0));
        });

        // Handle change to theme
        $editorTheme.on('change', function(e){
            replaceTheme($editor, $(this).val());
        });
    };

    function replaceLanguage(elements, replacement)
    {
      if( !Array.isArray(elements) ){
          elements = [elements];
      }

      elements.forEach(function($el){
        replaceClass($el, 'language-?[\\w]+', 'language-' + replacement);
      });
    }

    function replaceTheme(elements, replacement)
    {
      if( !Array.isArray(elements) ){
          elements = [elements];
      }

      elements.forEach(function($el){
          replaceClass($el, 'prism-?[\\w]*$', replacement);
      });
    }

    function replaceClass($el, original, replacement)
    {
      var regExp = new RegExp(original);
      $el.removeClass(function(i, className){
          console.log('className.match(regExp):', className.match(regExp));
          return (className.match(regExp) || []).join(' ');
      }).addClass(replacement);
    }

})(jQuery);
