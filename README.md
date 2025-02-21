# Slim Todo List API

> Simple Todo List RESTful API built with Slim framework.

## Table of Contents

- [General Info](#general-information)
- [Technologies Used](#technologies-used)
- [Features](#features)
- [Setup](#setup)
- [Usage](#usage)
- [Authentication](#authentication)
- [Rate and Usage Limits](#rate-and-usage-limits)
- [HTTP Response Codes](#http-response-codes)
- [Project Status](#project-status)
- [Acknowledgements](#acknowledgements)

## General Information

Slim Todo List API is a simple RESTful API that allows users to manage their to-do list. It supports pagination, sorting and filtering by status. This API uses [JWT](https://jwt.io/) for authentication. This project is designed to explore and practice working with the CRUD Operation, RESTful API, Data Modeling, Authentication, and Database in PHP.

## Technologies Used

- PHP - version 8.3.6
- MySQL - version 8.0.4
- [Slim](https://www.slimframework.com/) 4

## Features

- **User Registration**: Register a new user using the `POST` method.
- **User Login**: Authenticate the user using the `POST` method.
- **Create a To-Do Item**: Create a new to-do item using the `POST` method.
- **Update a To-Do Item**: Update an existing to-do item using the `PUT` method.
- **Delete a To-Do Item**: Delete an existing to-do item using the `DELETE` method.
- **Get To-Do Items**: Get the list of to-do items with pagination using the `GET` method.
- **Filtering and Sorting**: Get the list of to-do items by status or sort by specific field using the `status` and `sort` query params.
- **Refresh Token**: Get a new access token using the `POST` method.

## Setup

To run this CLI tool, you’ll need:

- **PHP**: Version 8.3 or newer
- **MySQL**: Version 8.0 or newer
- **Composer**: Version 2.7 or newer

How to install:

1. Clone the repository

   ```bash
   git clone https://github.com/krisnaajiep/slim-todo-list-api.git
   ```

2. Change the current working directory

   ```bash
   cd slim-todo-list-api
   ```

3. Install dependecies

   ```bash
   composer install
   ```

4. Configure `.env` file for database configuration.

   ```bash
   cp .env.example .env
   ```

   ```dotenv
   # DATABASE CONFIG
   DB_HOST=localhost
   DB_PORT=3306
   DB_USER=root
   DB_PASS=
   DB_NAME=todo
   ```  

5. Generate JWT secret key

   ```bash
   php cli.php jwt:secret
   ```

6. [Start MySQL server](https://phoenixnap.com/kb/start-mysql-server)

7. Run the PHP built-in Web Server

   ```bash
   cd public
   php -S localhost:8888
   ```

## Usage

Example API Endpoints:

1. **User Registration**

   - Method: `POST`
   - Endpoint: `/register`
   - Request Body:

     - `name` (string) - The name of the user.
     - `email` (string) - The email address of the user.
     - `password` (string) - The password of the user account.

   - Example Request:

     ```http
     POST /register
     {
       "name": "John Doe",
       "email": "john@doe.com",
       "password": "example_password",
     }
     ```

   - Response:

     - Status: `201 Created`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
       "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
       "token_type": "Bearer",
       "expires_in": 3600
     }
     ```

2. **User Login**

   - Method: `POST`
   - Endpoint: `/login`
   - Request Body:

     - `email` (string) - The email address of the user.
     - `password` (string) - The password of the user account.

   - Example Request:

     ```http
     POST /login
     {
       "email": "john@doe.com",
       "password": "example_password",
     }
     ```

   - Response:

     - Status: `200 OK`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
       "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
       "token_type": "Bearer",
       "expires_in": 3600
     }
     ```

3. **Refresh Token**

   - Method: `POST`
   - Endpoint: `/refresh`
   - Request Header:

     - `Authorization` (Bearer) - The refresh token.

   - Response:

     - Status: `200 OK`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
       "message": "Token refreshed",
       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
       "token_type": "Bearer",
       "expires_in": 3600
     }
     ```

4. **Create a To-Do Item**

   - Method: `POST`
   - Endpoint: `/todos`
   - Request Header:

     - `Authorization` (Bearer) - The access token.

   - Request Body:

     - `title` (string) - The title of the todo item.
     - `description` (string) - The description of the todo item.

   - Example Request:

     ```http
     POST /todos
     {
      "title": "Buy groceries",
      "description": "Buy milk, eggs, and bread"
     }
     ```

   - Response:

     - Status: `201 Created`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
       "id": 1,
       "title": "Buy groceries",
       "description": "Buy milk, eggs, and bread"
     }
     ```

5. **Update an Existing To-Do Item**

   - Method: `PUT`
   - Endpoint: `/todos/{id}`
   - Request Header:

     - `Authorization` (Bearer) - The access token.

   - Request Body:

     - `title` (string) - The title of the todo item.
     - `description` (string) - The description of the todo item.
     - `status` (string) - The status of the todo item.

   - Example Request:

     ```http
     POST /todos
     {
      "title": "Buy groceries",
      "description": "Buy milk, eggs, bread, and cheese",
      "status": "in progress"
     }
     ```

   - Response:

     - Status: `200 OK`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
       "id": 1,
       "title": "Buy groceries",
       "description": "Buy milk, eggs, bread, and cheese",
       "status": "in progress"
     }
     ```

6. **Delete an Existing To-Do Item**

   - Method: `DELETE`
   - Endpoint: `/todos/{id}`
   - Request Header:

     - `Authorization` (Bearer) - The access token.

   - Response:

     - Status: `204 No Content`
     - Content-Type: `text/xml`

7. **Get To-Do Items**

   - Method: `GET`
   - Endpoint: `/todos`
   - Request Header:
  
     - `Authorization` (Bearer) - The access token.

   - Response:

     - Status: `200 OK`
     - Content-Type: `application/json`

   - Example Response:

     ```json
     {
        "data": [
          {
            "id": 1,
            "title": "Buy groceries",
            "description": "Buy milk, eggs, bread, and cheese"
            "status": "in progress",
            "created_at": "2025-01-25 21:11:34",
            "updated_at": "2025-01-25 21:12:18"
          },
          {
            "id": 2,
            "title": "Pay bills",
            "description": "Pay electricity and water bills",
            "status": "todo",
            "created_at": "2025-01-25 21:11:42",
            "updated_at": "2025-01-25 21:12:55"
          }
        ],
        "page": 1,
        "limit": 10,
        "total": 2
     }
     ```

     - Params:

       - `page` - The page number to retrieve in a paginated list of results.
       - `limit` - Specifies the maximum number of items to be returned in the response.
       - `status` - Used for filtering to-do items based on their status.
       - `sort` - Used for sorting to-do items based on spesific field.

## Authentication

This API uses Bearer Token for authentication. You can generate an access token by registering a new user or login.

You must include an access token in each request to the API with the Authorization request header.

### Authentication error response

If an API key is missing, malformed, or invalid, you will receive an HTTP 401 Unauthorized response code.

## Rate and Usage Limits

API access rate limits apply at a per-API key basis in unit time. The limit is 60 requests per minute. Also, depending on your plan, you may have usage limits. If you exceed either limit, your request will return an HTTP 429 Too Many Requests status code.

Each API response returns the following set of headers to help you identify your use status:

| Header                  | Description                                                                       |
| ----------------------- | --------------------------------------------------------------------------------- |
| `X-RateLimit-Limit`     | The maximum number of requests that the consumer is permitted to make per minute. |
| `X-RateLimit-Remaining` | The number of requests remaining in the current rate limit window.                |
| `X-RateLimit-Reset`     | The time at which the current rate limit window resets in UTC epoch seconds.      |

## HTTP Response Codes

The following status codes are returned by the API depending on the success or failure of the request.

| Status Code               | Description                                                                                  |
| ------------------------- | -------------------------------------------------------------------------------------------- |
| 200 OK                    | The request was processed successfully.                                                      |
| 201 Created               | The new resource was created successfully.                                                   |
| 400 Bad Request           | The server could not understand the request due to invalid syntax.                           |
| 401 Unauthorized          | Authentication is required or the access token is invalid.                                   |
| 403 Forbidden             | Access to the requested resource is forbidden.                                               |
| 404 Not Found             | The requested resource was not found.                                                        |
| 409 Conflict              | Indicates a conflict between the request and the current state of a resource on a web server |
| 429 Too Many Request      | The client has sent too many requests in a given amount of time (rate limiting).             |
| 500 Internal Server Error | An unexpected server error occurred.                                                         |

## Project Status

Project is: _complete_.

## Acknowledgements

This project was inspired by [roadmap.sh](https://roadmap.sh/projects/todo-list-api).
