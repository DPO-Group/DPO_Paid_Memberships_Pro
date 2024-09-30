# DPO_Paid_Memberships_Pro

## DPO Pay Paid Memberships Pro plugin v1.0.1 for Paid Memberships Pro v3.1.3

This is the DPO module for Paid Memberships Pro. Please feel free
to [contact the DPO support team](https://dpogroup.com/contact-us/) should you require any assistance.

## Installation

1. Unzip the module to a temporary location on your computer.
2. On Filezila copy the **wp-content** folder in the archive to your base WordPress folder. This will merge the folders in the DPO module with your WordPress folders. You will be prompted to overwrite the paymentsettings.php file, select overwrite.
3. Locate the paid-memberships-pro.php file in the root directory of the Paid Memberships Pro plugin.
4. Add the following line of code before the first require_once statement:
  ```php
require_once(PMPRO_DIR . "/classes/gateways/class.pmprogateway_dpo.php");
```
5. If present, comment out the require_once line for Payfast by adding // at the beginning:
  ```php
// require_once(PMPRO_DIR . "/classes/gateways/class.pmprogateway_payfast.php");
```
6. Search for the array $pmpro_gateways. Add the following entry to the end of the array:
  ```php
'dpo' => __('DPO', 'pmpro'),
```
7. If present, comment out the PayFast entry by adding // at the beginning:
  ```php
// 'payfast' => __('PayFast', 'pmpro'),
```
8. Log in to the administration console of your website. Select **Memberships** from the menu, and go to **Payment Settings**. Under **Payment Settings** select **Payment Gateway and SSL**. Choose **DPO** from the **Payment Gateway** drop down menu. The DPO options will then be shown below. Enter your credentials and options and then click **Save Changes**.


Please [click here](https://github.com/DPO-Group/DPO_Paid_Memberships_Pro) for more information concerning this
module.

## Collaboration

Please submit pull requests with any tweaks, features or fixes you would like to share.
