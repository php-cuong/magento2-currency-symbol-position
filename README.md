# How to change currency symbol position in Magento 2 (Left to Right or Right to Left)
When you work for a project, you are required to move the currency symbol from left to right or from right to left, you don't know how to complete this task, you are searching for a solution. This extension is the best to complete your task.

# How to install this extension?

Under your root folder, run the following command lines:

- composer require php-cuong/magento2-currency-symbol-position
- php bin/magento setup:upgrade --keep-generated
- php bin/magento setup:di:compile
- php bin/magento cache:flush

# How to see the results

1. Go to the backend

On the Magento Admin Panel, you navigate to the Stores → Currency → Currency Symbol Position

2. Go to the storefront

Check the product price.
