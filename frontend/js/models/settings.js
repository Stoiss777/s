App.models.settings = 
{

    ttl: 1, // <--for tests //3600000 * 24,  // Время жизни настроек у клиента
            
    initialize: function()
    {
        var cTime = parseFloat(localStorage.getItem("settings.__time"));
        if ( !cTime || (cTime + this.ttl < (new Date).getTime()) )
        {
            $.getJSON("http://api.invitations.stoiss.net/v0/setting.json?key=*", function(data)
            {
                if ( data )
                {
                    $.each(data, function(key, value)
                    {
                        localStorage.setItem("settings." + key, value);
                    });
                    localStorage.setItem("settings.__time", (new Date).getTime());
                }
            });
        }
    },
    
    get: function(key)
    {
        return localStorage.getItem("settings." + key);
    },
    
    set: function(key, value)
    {
        localStorage.setItem("settings." + key, value);
    }

            
};

App.models.settings.initialize();