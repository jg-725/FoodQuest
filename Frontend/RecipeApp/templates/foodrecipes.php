<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<head>
    <title>FoodQuest</title>
     
    <style>
        body {
            text-align: center;
            margin: 40px;
            font-family: Arial, Helvetica, sans-serif;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            display: inline-block;
            text-align: center;
            margin: 10px;
            width: 200px;
        }
        .recipe-title {
            font-size: 16px;
            line-height: 1.2;
            max-height: 3.6em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }
        img {
            display: block;
            margin: 0 auto;
            max-width: 200px;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: blue;
            text-decoration: underline;
        }
        .home-button {
            display: inline-block;
            margin-top: 10px;
            margin-left: 10px;
            color: blue;
            text-decoration: underline;
        }
        .recipe-details {
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #ccc;
            padding: 20px;
        }
        .section-title {
            text-align: left;
            margin-bottom: 10px;
        }
        .ingredient-list, .instrction-list {
            text-align: left;
        }
    </style>
</head>

<body>
  <?php
    // Retrieve the username from the query string
    $username = $_GET["username"] ?? "";

    ?>

    <!-- Add more content or features as needed -->
    
    <h1>FoodQuest</h1>
    <form method="POST" action="/">
        <input type="text" name="search_query" placeholder="Search for recipes">
        <input type="submit" value="Search">
        <a href="/home" class="home-button">Home</a>
    </form>

    <!-- Check of recipes are available-->
    {% if recipes%}
    <h2>Results for "{{ search_query }}"</h2>
    <ul>
        {% for recipe in recipes %}
        <li>
            <h3 class="recipe-title">{{ recipe.title }}</h3>
            {% if recipe.image %}
            <img src="{{ recipe.image }}" alt="{{ recipe.title }}">
            {% endif %}
            <a href="{{ url_for('view_recipe', recipe_id=recipe.id, search_query=search_query) }}">View</a>
        </li>
        {% endfor %}
    </ul>
    <!-- If no recipes are found -->
    {% else %}
    <p>No recipes found.</p>
    {% endif %}
</body>
</html>
