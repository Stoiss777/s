$(function() {

    App.widgets.content = new(Backbone.View.extend({

        el: $("#content"),

        model: App.models.page,

        initialize: function()
        {
            this.model.bind('change', this.render, this);
        },

        render: function()
        {
            $el = $("#content");
            switch ( this.model.get("content") )
            {
                case "signup":
                {
                    App.getHtml("Signup", function(data) 
                    {
                        $el.html(data);
                    });
                    App.getController("Signup");
                }
            }
        }

    }));


    App.models.page.trigger("change");


})