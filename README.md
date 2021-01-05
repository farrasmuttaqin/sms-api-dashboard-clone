# SMS API DASHBOARD

<img alt="GitHub top language" src="https://img.shields.io/github/languages/top/farrasmuttaqin/sms-api-dashboard-clone">  <img alt="GitHub repo size" src="https://img.shields.io/github/repo-size/farrasmuttaqin/sms-api-dashboard-clone">  <img alt="GitHub last commit" src="https://img.shields.io/github/last-commit/farrasmuttaqin/sms-api-dashboard-clone">

P.S. This is just a clone version of SMS API DASHBOARD 

## Getting Started
SMS API Dashboard is a web-based system which assists the user of SMS API services to generate and observe the report of SMS delivery status. This system authorizes users to generate and download SMS delivery reports and also manage the accounts.

## Requirements
* PHP 7.0.0++ is required but using the latest version of PHP 7 is highly recommended.
* Composer 1.0.0++.
* Laravel Framework 5.5.50

## Installations
```bash
composer install
```

## Showcases

![Login Page](https://raw.githubusercontent.com/farrasmuttaqin/sms-api-dashboard-clone/Task-2-First_time_push_sms_api_dashboard/screenshoot/login.png)
<p align="center">Figure 1: Login Page</p>

Login page used to sign in into the system, to prevent bots/robots to login therefore used captcha function.

![Dashboard Page](https://raw.githubusercontent.com/farrasmuttaqin/sms-api-dashboard-clone/Task-2-First_time_push_sms_api_dashboard/screenshoot/dashboard.png)
<p align="center">Figure 2: Dashboard Page</p>

Dashboard page is to displays the summary of SMS delivery status in doughnut diagram forms and list, these diagrams display the total SMS processed by the system. The diagrams are using different colours according to the delivery status.

![Reports Page](https://raw.githubusercontent.com/farrasmuttaqin/sms-api-dashboard-clone/Task-2-First_time_push_sms_api_dashboard/screenshoot/report.png)
<p align="center">Figure 3: Report Page</p>

The Reports page provide access to the user to request new reports and a portal to the previously generated reports. A report in the SMS API Dashboard is a summary of SMS’es sent with the 1rstWAP  SMS API. The reports include the delivery status information whether the SMS API sent the SMS, the SMS API delivered the SMS to the Mobile Network Operator or the SMS API could not commit the SMS due to undelivered or rejected state.