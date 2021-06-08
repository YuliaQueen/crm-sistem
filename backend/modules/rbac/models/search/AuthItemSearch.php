<?php

namespace backend\modules\rbac\models\search;

use dosamigos\arrayquery\ArrayQuery;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * @property string $name
 * @property string $description
 * @property int $pageSize
 */
class AuthItemSearch extends Model
{
    /**
     * @var string auth item name
     */
    public string $name = '';

    /**
     * @var string auth item description
     */
    public string $description = '';

    /**
     * @var int the default page size
     */
    public int $pageSize = 25;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'description'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Название',
            'description' => 'Описание',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ArrayDataProvider
     */
    public function search(array $params): ArrayDataProvider
    {
        $authManager = Yii::$app->getAuthManager();

        $items = $authManager->getRoles();

        $query = new ArrayQuery($items);

        $this->load($params);

        if ($this->validate()) {
            $query->addCondition('name', $this->name ? "~{$this->name}" : null)
                ->addCondition('description', $this->description ? "~{$this->description}" : null);
        }

        return new ArrayDataProvider([
            'allModels' => $query->find(),
            'sort' => [
                'attributes' => ['name'],
            ],
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);
    }
}
