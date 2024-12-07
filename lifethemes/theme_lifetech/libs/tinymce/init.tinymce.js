tinyMCE.init({
        selector: ".lifetech_editor",
        toolbar: 'undo redo | styleselect forecolor| bold italic underline bullist numlist outdent indent',
        plugins: 'advlist  autolink   lists preview link code wordcount',
        branding: false,
        oninit : myCustomOnInit(),
        browser_spellcheck : true,
        setup: function (editor) {
           editor.on('SaveContent', function (ed) {
              ed.content = ed.content.replace("'", "&#39;").replace("'", "&#39;");
           });
        },
        /*default_link_target: '_blank',
        link_default_protocol: 'https',
        oninit : myCustomOnInit(),
        selector: ".tinymce",
        plugins: 'lists advlist link',
        branding: false,
        default_link_target: '_blank',
        link_default_protocol: 'https',
        oninit : myCustomOnInit(),*/
        /*toolbar1: 'undo redo | styleselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link  ',
        toolbar2: 'bold italic underline strikethrough superscript subscript | fontselect fontsizeselect forecolor backcolor ',*/

    });

    function myCustomOnInit() {
        
        //console.log("Textarea editor initiated!!");
    }
