<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class GetUsersRequest extends FormRequest
    {
        public function authorize()
        {
            return true;
        }

        public function rules()
        {
            return [
                'page' => 'integer|min:1',
                'count' => 'integer|min:1',
            ];
        }

        public function messages()
        {
            return [
                'page.integer' => 'Page must be a number.',
                'count.integer' => 'Count must be a number.',
            ];
        }


        /**
         * Get the validated input data with default values.
         *
         * @return array
         */
        public function validatedWithDefaults()
        {
            return [
                'page' => $this->input('page', 1),
                'count' => $this->input('count', 5),
            ];
        }
    }
