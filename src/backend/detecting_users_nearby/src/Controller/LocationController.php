<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/// this code is contributed by
/// https://www.geeksforgeeks.org/program-distance-two-points-earth/
/**
 * Class LocationController
 * @package App\Controller
 * @Route("/api",name="location_api")
 */
class LocationController extends AbstractController
{
    /**
     * @Route("/location", name="location")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LocationController.php',
        ]);
    }

    /**
     * @param Request $request
     * @param LocationRepository $repository
     * @param $longitudeFrom
     * @param $latitudeFrom
     * @return JsonResponse
     * @Route("/process",name="process_nearby_users",methods={"POST"})
     */
    public function process(Request $request,LocationRepository $repository,$longitudeFrom,$latitudeFrom)
    {
        $data = [];
        $client_city = $this->getCityByIp($request->getClientIp());
        $locations = $repository->findAll();
        if (is_null($client_city)){
            $data = [
                'status' => 404,
                'errors' => "city not found"
            ];
            return $this->response($data,404);
        }
        foreach ($locations as $location){
            if ($location->getCity() == $client_city){
                $distance = $this->compareUserDistance($latitudeFrom,$longitudeFrom, $location->getLatitude(), $location->getLongitude());
                if ($distance <= 20){
                    $data = [
                        'user' => $location->getUser()->getUsername(),
                    ];
                }
            }
        }
        return $this->response($data);
    }

    /**
     * @param LocationRepository $repository
     * @return JsonResponse
     * @Route("/locations",name="locations",methods={"GET"})
     */
    public function getLocations(LocationRepository $repository)
    {
        $data = $repository->findAll();

        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @Route("/add",name="add_location",methods={"POST"})
     */
    public function addLocation(Request $request,EntityManagerInterface $entityManager)
    {
        try{
            $request = $this->transformJsonBody($request);
            if (!$request || !$request->get('longitude') || !$request->get('latitude') || !$request->get('user')){
                throw new \Exception();
            }

            $city = $this->getCityByIp($request->getClientIp());
            if (is_null($city)){
                throw new \Exception();
            }
            $location = new Location();
            $location->setLatitude($request->get('latitude'));
            $location->setLongitude($request->get('longitude'));
            $location->setUser($request->get('user'));
            $location->setCity($city);
            $entityManager->persist($location);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "post added successfully"
            ];
            return $this->response($data);

        }catch (\Exception $exception){
            $data = [
                'status' => 422,
                'errors' => "Data no valid"
            ];
            return $this->response($data,422);
        }
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LocationRepository $repository
     * @param $id
     * @Route("/location/{id}",name="location_put",methods={"PUT"})
     * @return JsonResponse
     */
    public function updateLocation(Request $request,EntityManagerInterface $entityManager,LocationRepository $repository,$id)
    {
        try{
            $location = $repository->find($id);
            if (!$location){
                $data = [
                    'status' => 404,
                    'errors' => 'location not found',
                ];
                return $this->response($data,404);
            }

            $request = $this->transformJsonBody($request);
            if (!$request || !$request->get('longitude') || !$request->get('latitude') || !$request->get('user')){
                throw new \Exception();
            }

            $city = $this->getCityByIp($request->getClientIp());
            if (is_null($city)){
                throw new \Exception();
            }
            $location->setLatitude($request->get('latitude'));
            $location->setLongitude($request->get('longitude'));
            $location->setUser($request->get('user'));
            $location->setCity($city);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "location updated successfully!"
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid"
            ];
            return $this->response($data,422);
        }
    }

    /**
     * @Route("/location/{id}",name="location_get",methods={"GET"})
     * @param LocationRepository $repository
     * @param $id
     * @return JsonResponse
     */
    public function getLocation(LocationRepository $repository,$id)
    {
        $location = $repository->find($id);
        if (!$location){
            $data = [
                'status' => 404,
                'errors' => "location not found",
            ];
            return $this->response($data,404);
        }
        return $this->response($location);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param LocationRepository $repository
     * @param $id
     * @return JsonResponse
     * @Route("/location/{id}",name="location_deleted",methods={"DELETE"})
     */
    public function deleteLocation(EntityManagerInterface $entityManager,LocationRepository $repository,$id)
    {
        $location = $repository->find($id);
        if (!$location){
            $data = [
                'status' => 404,
                'errors' => "location not found",
            ];
            return $this->response($data,404);
        }
        $entityManager->remove($location);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'errors' => "location deleted successfully!",
        ];
        return $this->response($data);
    }

    /**
     * @param $latFrom
     * @param $longFrom
     * @param $latTo
     * @param $longTo
     * @return float
     */
    public function compareUserDistance($latFrom,$longFrom,$latTo,$longTo)
    {
        $longitude_from = deg2rad($longFrom);
        $longitude_to   = deg2rad($longTo);
        $latitude_from = deg2rad($latFrom);
        $latitude_to   = deg2rad($latTo);

        $dlong = $longitude_from - $longitude_to;
        $dlat  = $latitude_from  - $latitude_to;

        $val = pow(sin($dlat/2),2) + cos($latitude_from) * cos($latitude_to) * pow(sin($dlong/2),2);

        $res = 2 * asin(sqrt($val));
        $radius = 3958.756;

        // convert distance with miles unit.
        $distance_miles = $res * $radius;
        // convert miles to km. 1Km is equivalent to 0.62137 miles.
        $distance = $distance_miles / 0.62137;

        return round($distance);
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function getCityByIp($ip)
    {
        $ipdata = @unserialize(file_get_contents("http://ip-api.com/php/" . $ip));
        if($ipdata && $ipdata['status'] == 'success'){
            return $ipdata['city'];
        }
        return null;
    }

    /**
     * Returns a json response
     * @param $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data,$status = 200,$headers = [])
    {
        return new JsonResponse($data,$status,$headers);
    }

    /**
     * @param Request $request
     * @return Request
     */
    public function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        if ($data === null){
            return $request;
        }
        $request->request->replace($data);
        return $request;
    }
}