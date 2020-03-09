-Installation:
Please upload the module under app/code/Howard/NewsletterCoupon/
Afterwards, please run:
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush

-Configuration:
1. Please create your discount Cart Price Rules.
2. Go to Stores-> Configuration -> Howard -> Newsletter Coupon. Enable the module, and input your Cart Price Rule id.
3. Flush your magento cache

-Test it:
1. Subscribe your email to the newsletter on your Magento Homepage.
2. Check if your Cart Price Rule generates a unique coupon code.
3  You should receive the coupon code in your email.
