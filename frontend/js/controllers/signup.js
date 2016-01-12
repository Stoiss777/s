function Signup()
{

    this.go = function(service)
    {
        switch ( App.models.settings.get("services." + service + ".auth.type") )
        {
            case 'oauth2':
            {
                this.oauth2(service);
                break;
            }
        }
    };
    
    this.oauth2 = function(service)
    {
        var target = App.models.settings.get("services." + service + ".auth.target");
        var uri = App.models.settings.get("services." + service + ".auth.codeUri");  // !!!
        switch ( target )
        {
            case 'popup':
            {
                console.log(uri);
                var params = App.models.settings.get("services." + service + ".auth.popupParams");
                window.open(uri, "oauth2", params? params: "width=100,height=100");
                break;
            }
        }
    };
    
    this.ready = function()
    {
        $("[data-signup-service]").each(function()
        {
            $(this).click(function()
            {
                App.controller.go($(this).attr('data-signup-service'));
                return false;
            });
        });
    };
    
}

