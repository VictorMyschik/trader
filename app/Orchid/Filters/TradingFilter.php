<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\Lego\ActionFilterPanel;
use App\Models\System\Settings;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class TradingFilter extends Filter
{
    public const array FIELDS = [];

    public static function runQuery(): iterable
    {
        return Trade::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public static function displayFilterCard(): Rows
    {
        return Layout::rows([
            Group::make([
                Select::make('active')
                    ->options([null => 'Все', 1 => 'active', 0 => 'not active'])
                    ->value(request()->get('active'))
                    ->title('Активно'),

                Select::make('category')
                    ->options(self::getCategoryList())
                    ->multiple()
                    ->value(request()->get('category'))
                    ->title('Type'),

                Input::make('name')->value(request()->get('name'))->title('Наименование'),
                Input::make('code_key')->value(request()->get('codeKey'))->title('Key (in code)'),
                Input::make('value')->value(request()->get('value'))->title('Value'),
            ]),

            ViewField::make('')->view('space'),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }

    private static function getCategoryList(): array
    {
        $category = array_unique(array_column(Settings::getSettingList(), 'category'));

        return array_combine($category, $category);
    }
}
