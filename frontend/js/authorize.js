var Authorize =
{
    
    OAuth2: function(settings)
    {
        this.settings = settings;

        this.go = function()
        {
            switch ( this.settings.target )
            {
                case 'popup':
                {
                    window.open(this.settings.uri, "authorize.vk");
                    break;
                }
            }
        };
    }
            
};