ECOMMERCE SHOPPING CART PLUGINS
DPO Paid Memberships Pro plugin v1.0.0 for Paid Memberships Pro v3.0.4

Thank you for downloading the DPO Pay plugin for Paid Memberships Pro.
Along with this integration guide, your download should also have included your plugin in a .zip
(or compressed) format. In order to install it, kindly follow the steps and instructions outlined below, and feel free to contact the DPO Pay support team at https://dpogroup.com/contact-us/ should you require any assistance.

INSTALLATION INSTRUCTIONS

STEP 1
- Unzip the module to a temporary location on your computer.

STEP 2
- On Filezila copy the "wp-content" folder in the archive to your base WordPress folder.
- This will merge the folders in the DPO module with your WordPress folders.
- You will be prompted to overwrite the paymentsettings.php file, select overwrite.

STEP 3
- Locate the paid-memberships-pro.php file in the root directory of the Paid Memberships Pro plugin.
- Add the following line of code before the first require_once statement:
  require_once(PMPRO_DIR . "/classes/gateways/class.pmprogateway_dpo.php");

STEP 4
- If present, comment out the require_once line for Payfast by adding // at the beginning:
  // require_once(PMPRO_DIR . "/classes/gateways/class.pmprogateway_payfast.php");

STEP 5
- Search for the array $pmpro_gateways.
- Add the following entry to the end of the array:
  'dpo' => __('DPO', 'pmpro'),
- If present, comment out the PayFast entry by adding // at the beginning:
  // 'payfast' => __('PayFast', 'pmpro'),

STEP 4
- Log in to the administration console of your website.
- Select "Memberships" from the menu, and go to "Payment Settings".
- Under "Payment Settings" select "Payment Gateway and SSL".
- Choose "DPO" from the "Payment Gateway" drop down menu.
- The DPO options will then be shown below.
- Enter your credentials and options and then click "Save Changes".

Please feel to contact the DPO Pay support team at https://dpogroup.com/contact-us/ should you have any queries or concerns.

DPO PAY WILL NOW BE AVAILABLE AS A PAYMENT METHOD ON THE CHECKOUT PAGE.

DISCLAIMER:
DPO Group has developed and tested the compatibility of this shopping cart plugin in line with the listed shopping cart version and relevant DPO Group API’s. DPO Group does not guarantee that the shopping cart plugin will work in your environment. DPO Group has no obligation to make changes to the shopping cart plugin should it not work in your environment. Should development be required to accommodate your environment,
DPO Group reserves the right to bill a development fee.

COPYRIGHT © 2024 DPO Group
