var App = 
{
    
    apiUrl: "http://api.invitations.stoiss.net/v0",  // url к api
    
    controller: null,    // хранится текущий контроллер страницы (js скрипт отвечающий за эту страницу)
    
    router: null,        // backbone роутер
    
    widgets: { },        // backbone views
    
    models: { },         // backbone models
    
    loading: 0,          // количество объектов которые загружаются в данный момент
    
    funcs: [],
    
    scripts: [],
    
    ready: function(func)
    {
        if ( func )
        {
            this.funcs[this.funcs.length] = func;
        }
        if ( !this.loading && this.funcs.length )
        {
            $.each(this.funcs, function(key, value)
            {
                value();
            });
            this.funcs = [];
        }
    },
    
    getScript: function(file, success)
    {
        // если скрипт уже был загружен
        $.each(this.scripts, function(key, value) 
        {
            if ( value === file )
            {
                if ( success )
                {
                    success();
                }
            }
            App.ready();
            return;
        });
        // если не был
        App.loading++;
        $.getScript(file, function()
        {
            App.loading--;
            App.scripts[App.scripts.length] = file;
            if ( success )
            {
                success();
            }
            App.ready();
        });
    },
    
    // Контроллер всегда нужно загружать в конце, после моделей, представлений и пр.
    // Иначе может сработать ready() раньше времени
    getController: function(name)
    {
        this.getScript("/js/controllers/" + name.toLowerCase() + ".js", function()
        {
            App.controller = eval("new " + name.charAt(0).toUpperCase() + name.slice(1) + "()");
            App.ready(function() 
            {
                App.controller.ready();
            });
        });
    },
    
    getModel: function(name)
    {
        this.getScript("/js/models/" + name.toLowerCase() + ".js");
    },
    
    getWidget: function(name)
    {
        this.getScript("/js/widgets/" + name.toLowerCase() + ".js");
    },
    
    getHtml: function(name, success)
    {
        App.loading++;
        $.get("/html/" + name.toLowerCase() + ".html", function(data, textStatus)
        {
            App.loading--;
            if ( success )
            {
                success(data, textStatus);
            }
            App.ready();
        });
    },
    
    
    initialize: function()
    {
        // ______ Модель состояния текущей страницы ____________
        App.models.page = new (Backbone.Model.extend
        ({
            defaults:
            {
                content: "index"    // контент загруженный в блоке контент (проще говоря текущая страница)
            }
        }));
        // __________ Подгружаем самое необходимое ____________________
        App.getModel("settings");
        App.getWidget("content");
        // _________________ Router ______________________
        App.router = new (Backbone.Router.extend
        ({
            routes:
            {
                signup: "signup",
                token: "token"
            },

            initialize: function() 
            {
                Backbone.history.start
                ({
                    pushState: true
                });
            },

            signup: function()
            {
                App.models.page.set
                ({
                    content: "signup"
                });
            },
            
        }));
        // __________ Навешиваем pushState события на все ссылки _____________
        $(function()
        {
            $("a:not(.app-noroute)").on("click", function(event) 
            {
                var href = $(event.currentTarget).attr('href');
                if ( !event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey )
                {
                    event.preventDefault();
                    App.router.navigate(href, {trigger: true});
                    return false;
                }
            });
        });
    }
    
    
};


App.initialize();


