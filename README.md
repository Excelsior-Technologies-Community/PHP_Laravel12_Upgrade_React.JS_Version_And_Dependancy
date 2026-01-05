# PHP_Laravel12_Upgrade_React.JS_Version_And_Dependancy

# Step 1 : Install Laravel 12 and Create Project
# Using command
```php
Composer create-project laravel/laravel PHP_Laravel12_Upgrade_React.JS_Version_And_Dependancy
```
# Step 2 : Open Folder 
```php
Cd PHP_Laravel12_Upgrade_React.JS_Version_And_Dependancy
```
# Step 3: Now Install React.js 16 version
# Open Terminal and Using Command
```php
npm install react@16.14.0 react-dom@16.14.0
```
# Step 4 :Now Check This Version in package.json file
# Open package.json file
 <img width="1455" height="761" alt="image" src="https://github.com/user-attachments/assets/ce4a0f32-deb2-4831-903f-e8ab623eb629" />

# Step 5: Now Upgrade for React.js Latest Version and remove this old version
# Open Terminal and run command for remove old react.js version
```php
npm remove react react-dom
```
# Step 6 : Now Check Remove Old version react.js 
# Opne Terminal and run command
```php
npm list react react-dom
```
```php
Output
PHP_Laravel12_Upgrade_React.JS_Version_And_Dependancy@ C:\xampp\htdocs\PHP_Laravel12_Upgrade_React.JS_Version_And_Dependancy
└── (empty)
```
# Step 7: Upgrade for latest version in react.js 
# Open terminal and run command
```php
npm install react@latest react-dom@latest
```
# Now open package.json file and check latest version in react.js 
<img width="1398" height="755" alt="image" src="https://github.com/user-attachments/assets/25d1ed55-a293-488e-812e-78017b525122" />

 
