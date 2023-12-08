<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ recipe.title }}</title>
    <style>
        body {
            text-align: center;
            margin: 40px;
            font-family: Arial, Helvetica, sans-serif;
        }
        h1, h2 {
            margin-bottom: 20px;
        }
        ul, ol {
            text-align: left;
            list-style-position: inside;
            padding-left: 20px;
        }
        img {
            display: block;
            margin: 0 auto;
            max-width: 300px;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: blue;
            text-decoration: underline;
        }
        .recipe-details {
            max-width: 700px;
            margin: 0 auto;
            border: 2px solid #ccc;
            padding: 20px;
        }
        .section-title {
            text-align: left;
            margin-bottom: 10px;
        }
        .ingredient-list, .instruction-list {
            text-align: left;
        }
        
        
        
        #content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        #printBtn {
            display: block;
            margin: 20px 0;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;

    </style>
</head>
<body>


      <div id="content">
    <h1>Your Content Goes Here</h1>
    <p>This is a sample webpage that allows users to print or save as PDF.</p>
    <!-- Add your content here -->
</div>

<button id="pdfBtn">Print or Save as PDF</button>

<script>
    document.getElementById('pdfBtn').addEventListener('click', function () {
        // Get the content from the #content element
        var content = document.getElementById('content').innerHTML;

        // Make a POST request to the API endpoint
        fetch('https://127.0.0.1:5000/recipe/645514?search_query=salad/templates/generate_pdf.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `content=${encodeURIComponent(content)}`,
        })
        .then(response => response.blob())
        .then(blob => {
            // Create a download link and trigger the click event
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'output.pdf';
            link.click();
        })
        .catch(error => console.error('Error:', error));
    });
</script>

        
        



    <div class="recipe-details">
        <h1>{{ recipe.title }}</h1>

<div id="content">
    
    <!-- Add your content here -->




        <!-- Display the recipe image if available -->
        {% if recipe.image %}
        <img src="{{ recipe.image }}" alt="{{ recipe.title }}">
        {% endif %}

        <!-- Ingredients section -->
        <h2 class="section-title">Ingredients</h2>
        <!-- Create an unordered list for displaying the ingredients -->
        <ul class="ingredient-list">
            <!-- Loop through each ingredient in the recipe's extendedIngredients -->
            {% for ingredient in recipe.extendedIngredients %}
            <li>{{ ingredient.original }}</li>
            {% endfor %}
        </ul>

        <!-- Instructions Section -->
        <h2 class="section-title">Instructions</h2>
        <ol class="instruction-list">
            {% for step in recipe.analyzedInstructions[0].steps %}
            <li>{{ step.step }}</li>
            {% endfor %}
        </ol>
        <a href="/?search_query={{ search_query }}">Back to search results</a>
        
 
        
        
        
        
        
        
        
  
    </div>
</body>
</html>
