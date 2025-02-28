<?php

namespace App\Services;
use App\Models\Category;
use App\Models\House;
use App\Models\City;

class HouseService
{
    public function getCategoriesAndCities()
    {
        return [
            'categories' => Category::latest()->get(),
            'cities' => City::latest()->get(),
        ];
    }

    public function searchHouses($filters)
    {
        $query = House::query();

        if (!empty($filter['city'])){
            $query->where('city_id', $filters['city']);
        }

        if (!empty($filter['category'])){
            $query->where('category_id', $filters['category']);
        }

        $houses = $query->get();
        $category = Category::findOrFail($filters['category'] ?? null);
        $city = City::findOrFail($filters['city'] ?? null);

        return compact('houses', 'category', 'city');

    }

    public function getHouseDetails($house){
        $house->load(['photos','facilities', 'facilities.facility']);  
    return $house;
    }
}