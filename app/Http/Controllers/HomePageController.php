<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use App\Models\Contact;
class HomePageController extends Controller
{
    /**
     * Show the webiste Index page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $pageTitle = env('APP_NAME');
            return view('frontend.home.index',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }
    }

    public function international_patients()
    {
        try {
            $pageTitle = "International Patients";
            return view('frontend.home.international_pateints',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }
    }

    public function news_updates()
    {
        try {
            $pageTitle = "News & Updates";
            return view('frontend.home.news_updates',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }
    }

    public function about_us()
    {
        try {
            $pageTitle = "About us";
            return view('frontend.home.about-us',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }
    }

    public function contact_us()
    {
        try {
            $pageTitle = "Contact Us";
            return view('frontend.home.contact-us',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'HomePageController::contact_us[Page can\'t access.]');

        }
    }
    public function thankyoupage()
    {
        try {
            $pageTitle = "Thank you";
            return view('frontend.home.thankyoupage',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'HomePageController::contact_us[Page can\'t access.]');

        }
    }

    public function errorpage()
    {
        try {
            $pageTitle = "Error";
            return view('frontend.home.errorpage',compact('pageTitle'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'HomePageController::contact_us[Page can\'t access.]');

        }
    }

    public function submitContactForm(Request $request)
    {
        // Validate form data if needed

        // Get user's IP address
    $systemIP = $request->ip();

        $contact = Contact::create([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'system_ip' => $systemIP, // Store the user's IP address
        ]);

        try{



        if ($contact) {
            // Successfully inserted, get the last inserted ID
            $lastInsertedId = $contact->id;

            // Show success message (You can use Laravel's session to display the message)
            return redirect()->route('thank.you.page')->with('success', 'Contact details saved. ID: ' . $lastInsertedId);
        } else {
            // Handle failure if needed
            return redirect()->route('error.page')->with('error', 'Something went wrong. Please try again');
        }

    } catch (Exception $e) {
        return redirect()->route('error.page')->with('error', 'Something went wrong. Please try again');

    }
}





}