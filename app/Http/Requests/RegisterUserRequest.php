<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Http\Exceptions\HttpResponseException;
    use Illuminate\Contracts\Validation\Validator;

    class RegisterUserRequest extends FormRequest
    {
        public function authorize()
        {
            return true;
        }

        public function rules()
        {
            return [
                'name' => 'required|string|min:2|max:60',
                'email' => 'required|email:rfc,dns|unique:users,email',
                'phone' => 'required|regex:/^\\+380\\d{9}$/|unique:users,phone',
                'position_id' => 'required|exists:positions,id',
                'photo' => 'required|image|mimes:jpg,jpeg|max:5120|dimensions:min_width=70,min_height=70',
            ];
        }

        public function messages()
        {
            return [
                'name.required' => 'The name is required.',
                'name.min' => 'The name must be at least 2 characters.',
                'name.max' => 'The name may not be greater than 60 characters.',
                'email.required' => 'The email is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'User with this email already exists.',
                'phone.required' => 'The phone number is required.',
                'phone.regex' => 'The phone number format is invalid.',
                'phone.unique' => 'User with this phone already exists.',
                'position_id.required' => 'The position id is required.',
                'position_id.exists' => 'The selected position id is invalid.',
                'photo.required' => 'The photo is required.',
                'photo.image' => 'The photo must be an image.',
                'photo.mimes' => 'The photo must be a file of type: jpg, jpeg.',
                'photo.max' => 'The photo may not be greater than 5 Mbytes.',
                'photo.dimensions' => 'The photo must be at least 70px by 70px.',
            ];
        }

        protected function failedValidation(Validator $validator)
        {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }
    }
