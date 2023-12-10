<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ recipe.title }}</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

       .print-btn, .save-pdf-btn {
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;

    </style>
</head>
<body>
<script src="https://rawgit.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
        
  
   <h1> <p>FoodQuest Recipes</p></h1>

    <!-- Print Button -->
    <button class="print-btn" onclick="printPage()">Print or Save as PDF</button>
   
    <!-- Save as PDF Button -->
    <!--
    <button class="save-pdf-btn" onclick="saveAsPDF()">Save as PDF</button>
     -->
    <script>
        // Function to open the browser's print dialog
        function printPage() {
            window.print();
        }

        // Function to save the webpage as a PDF
        function saveAsPDF() {
            // Get the HTML content to be converted to PDF
            var content = document.body;

            // Configure the PDF options
            var options = {
                margin: 10,
                filename: 'saved_document.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 }
            };

            // Generate the PDF
            html2pdf().from(content).set(options).outputPdf().then(function(pdf) {
                // Download the generated PDF
                var blob = new Blob([pdf], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'saved_document.pdf';
                link.click();
            });
        }
    </script>      
        
        


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
