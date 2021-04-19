# First, Change permalink
Go to Setting -> Permalink and choose the "Post name" permalink structure. The default permalink will not work.

# Second, Enable API access in WooCommerce
The first step before you can use the WooCommerce API is to enable it in WordPress Admin. To enable the WooCommerce REST API, login to the backend of your WordPress site, hover over WooCommerce > Settings > Advanced. Next toggle the <b>Legacy API tab and select Enable the legacy REST API</b>.

# Third, Add a API keys with Read access only
The key that you generate in this process will authenticate your API requests. An API key ensures that WooCommerce serves only legitimate API requests.

WooCommerce > Settings > Advanced, click REST API to add a key. Enter the description, select the user and "Read" permissions and click on Generate API Key.

<b>Copy your Consumer key and Consumer secret to your External app like as Mobile or other Website</b>
This key allow your External app make a request to Woocommerce API as well as Wordpress REST API successfully by privilege account