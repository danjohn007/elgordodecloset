<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Restaurant;
use App\Models\Review;

class HomeController extends Controller
{
    private $restaurantModel;
    private $reviewModel;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
        $this->reviewModel = new Review();
    }

    public function index()
    {
        // Get featured restaurants
        $featuredRestaurants = $this->restaurantModel->getFeatured(6);
        
        // Get recent reviews
        $recentReviews = $this->reviewModel->getRecentReviews(5);
        
        // Get cuisine types for search
        $cuisineTypes = $this->restaurantModel->getCuisineTypes();
        
        $data = [
            'featured_restaurants' => $featuredRestaurants,
            'recent_reviews' => $recentReviews,
            'cuisine_types' => $cuisineTypes,
            'page_title' => 'Sistema de Reservas de Restaurantes'
        ];

        $this->view('home/index', $data);
    }

    public function about()
    {
        $data = [
            'page_title' => 'Acerca de Nosotros'
        ];

        $this->view('home/about', $data);
    }

    public function contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle contact form submission
            $validation = $this->validate([
                'name' => 'required|min:3',
                'email' => 'required|email',
                'subject' => 'required|min:5',
                'message' => 'required|min:10'
            ]);

            if ($validation) {
                // Process contact form (send email, save to database, etc.)
                $_SESSION['success'] = 'Su mensaje ha sido enviado correctamente. Le contactaremos pronto.';
                $this->redirect('/contact');
                return;
            }
        }

        $data = [
            'page_title' => 'Contacto'
        ];

        $this->view('home/contact', $data);
    }
}