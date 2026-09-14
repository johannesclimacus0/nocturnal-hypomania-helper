# Nocturnal Hypomania Helper

A web app that helps you organize your obsessive nighttime thoughts

## Features

- Group tasks by area, category, and task type, with difficulty and duration
- Create a session with tasks that fit a given time and filters
- Complete or skip session tasks
- Receive a session summary with completed, skipped, and unfinished counts
- Change UI font size

## Stack

- Laravel 13
- Laravel Fortify
- Vue 3
- Tailwind
- Laravel Horizon
- Laravel Reverb
- Laravel Sail
- PHP 8.4.1+

## Local setup

```bash
git clone https://github.com/johannesclimacus0/nocturnal-hypomania-helper.git
cp .env.example .env
composer install
./vendor/bin/sail build
./vendor/bin/sail up
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
./vendor/bin/sail npm run dev
./vendor/bin/sail artisan db:seed
```
Sign in with `test@example.org:password` for the demo after seeding

## Using the app

1. Create areas, categories, and task types, then add tasks
2. Start a session 
3. Work through the generated tasks, skip or complete them
4. Finish the session and review the summary

## Architecture

Controllers validate input through Form Requests, pass typed DTOs to action classes, then return JSON Resources.

Task selection uses a Pipeline to filter candidates and a Strategy to choose the next task. 
