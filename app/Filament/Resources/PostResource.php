<?php

namespace App\Filament\Resources;

use App\Models\Post;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\PostResource\Pages;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->required(),

                    // Category select dropdown
                    Select::make('categories_id')
                        ->label('Category')
                        ->options(Category::all()->pluck('name', 'id')->toArray())
                        ->required(),

                    // Featured image upload field
                    FileUpload::make('image')
                        ->label('Featured Image')
                        ->image()
                        ->directory('uploads/posts')
                        ->nullable(),

                    // Status dropdown for draft/published
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                        ])
                        ->default('draft')
                        ->label('Status')
                        ->required(),

                    // Optional slug field for SEO-friendly URLs
                    TextInput::make('slug')
                        ->unique()
                        ->label('Slug')
                        ->hint('Leave empty to auto-generate'),

                    // Link post to the user
                    Select::make('user_id')
                        ->label('Author')
                        ->relationship('user', 'name')
                        ->required(),
                ])->columns(2),

                // Description editor
                MarkdownEditor::make('description')->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->label('S/N')
                    ->getStateUsing(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('user.name')->label('Author')->sortable(),
                TextColumn::make('created_at')->label('Created At')->sortable(),
            ])
            ->filters([
                Filter::make('published')->query(fn ($query) => $query->where('status', 'published')),
                Filter::make('draft')->query(fn ($query) => $query->where('status', 'draft')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
