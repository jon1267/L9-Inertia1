<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Book::query()->paginate(7);

        return Inertia::render('books', ['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBookRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookRequest $request)
    {
        Validator::make($request->all(), [
            'title'  => 'required',
            'author' => 'required',
        ])->validate();

        Book::create($request->all());

        //$this->processImage($request);

        return redirect()->back()->with('message', 'Book was created' );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBookRequest  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        Validator::make($request->all(), [
            'title'  => 'required',
            'author' => 'required',
        ])->validate();

        $book->update($request->all());

        $this->processImage($request);

        return redirect()->back()->with('message', 'Book was updated' );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->back()->with('message', 'Book was deleted' );
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('imageFilepond')) {
            return $request->file('imageFilepond')->store('uploads/books', 'public');
        }
        return '';
    }

    protected function processImage(Request $request)
    {
        // here intent delete old picture on update. This generate error for Win (OSPanel+Win)
        // Need correct code: if picture update - delete old picture, than save new ...
        if ($image = $request->get('image')) {
            $path = storage_path('app/public/'.$image);
            if (file_exists($path)) {
                try {
                    //copy($path, public_path('storage/uploads/books')); //dd($path, $image, public_path($image));
                    copy($path, public_path($image)); //dd($path, $image, public_path($image));
                    unlink($path);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }

            }
        }
    }
}
