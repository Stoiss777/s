/*var settings =
{        
    
    
    
}
*/



var Signup = function()
{        
    
    this.go = function(service)
    {
        //var type = settings.get("vk.authorize.");
        var settings = 
        {
            type: settings.get('services.' + service + '.auth.type'),
            uri: settings.get('services.' + service + '.auth.codeUri'),  // !!!
            target: settings.get('services.' + service + '.auth.target')
        }
        console.log(settings); return false;
        switch ( settings.type )
        {
            case 'oauth':
            {
                var authorize = new Authorize.OAuth2(settings);
                authorize.go();
            }
        }
    }
    
    
    
}

    $(function()
    {
        $("[data-signup-service]").each(function()
        {
            console.log('!');
            //console.log($(this).attr('data-signup-service'));
            /*$(this).click(function()
            {
                _self.go(this.attr('data-signup'));
            });*/
        });
    });


//new Signup();


//
////var Settings = Backbone.Model.extend
//({
    
   // id: "vk",

    //url: "http://api.invaites.stoiss.net/v1/settings",
  
    /*sync: function(method, model, options) 
    {
        switch (method) 
        {
            case 'create':
            break;

            case 'update':
            break;

            case 'delete':
            break;

            case 'read':
            break;
        }
    }*/
  /*options || (options = {});

  switch (method) {
    case 'create':
    break;

    case 'update':
    break;

    case 'delete':
    break;

    case 'read':
    break;
  }*/

//});

//var settings = new Settings;
//settings.sync();
//console.log(settings.get('authorize.type'));
