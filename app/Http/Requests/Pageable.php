<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Pageable extends FormRequest {
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize(): bool {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules(): array {
    return [
      //
    ];
  }

  public function passedValidation() {
    $this->merge([
      "perPage" => $this->get('size') ?? null,
      // Will use in future
      //"columns" => $this->get('columns') ?? ['*'],
      "columns" => ['*'],
      // Will use in future
      //"pageName" => $this->get('page_name') ?? 'page',
      "pageName" => 'page',
      "page" => $this->get('page') ?? null,
    ]);
  }

  public function pagination(): array {
    return $this->only(['perPage', 'columns', 'pageName', 'page']);
  }
}
