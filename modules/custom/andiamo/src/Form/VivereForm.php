<?php

namespace Drupal\andiamo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VivereForm.
 */
class VivereForm extends FormBase {

  /**
   * Drupal\Core\Path\AliasManagerInterface definition.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pathAliasManager = $container->get('path.alias_manager');
    $instance->entityManager = $container->get('entity.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vivere_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nodeId=null) {

    if (isset($nodeId)) {
      $node = Node::load($nodeId);

      if ($node instanceof Node) {

        $form['nombre'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Nombre'),
          '#default_value' => $node->getTitle(),
          '#maxlength' => 64,
          '#size' => 64,
          '#weight' => 1,
          '#disabled' => TRUE
        ];

        $form['cantidad'] = array(
          '#type' => 'number',
          '#title' => $this->t('Cantidad'),
          '#default_value' => (int) $node->get('field_cantidad')->value,
          '#weight' => 2,
        );

        $form['stock'] = array(
          '#type' => 'number',
          '#title' => $this->t('Stock'),
          '#default_value' => (int) $node->get('field_stock')->value,
          '#weight' => 6,
          '#disabled' => TRUE
        );

        $form['node_id'] = [
          '#type' => 'hidden',
          '#value' => $node->id(),
        ];

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
          '#weight' => 15,
        ];
      } else {
        $form['message'] = array(
          '#type' => 'label',
          '#title' => $this->t('Este vivere no existe'),
          '#weight' => 6,
        );
      }
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $nodeId = $form_state->getValue('node_id');

    $cantidad = (int) $form_state->getValue('cantidad');

    if ($cantidad == 0 || $cantidad < 0 ) {
      $form_state->setErrorByName('cantidad', $this->t('Cantidad debe ser mayor a 0.'));
    } else {
      if (isset($nodeId)) {
        $node = Node::load($nodeId);

        if ($node instanceof Node) {

          $stock = (int) $form_state->getValue('stock');

          $nuevoStock = $stock + $cantidad;

          $node->set('title', $form_state->getValue('nombre'));
          $node->set('field_stock', $nuevoStock);

          // Update the 'Vivere'.
          $node->save();

        }
      }
    }

    parent::validateForm($form, $form_state);

    /*
    if (isset($nodeId)) {
      $node = Node::load($nodeId);

      if ($node instanceof Node) {

        $node->set('title', $form_state->getValue('nombre'));
        $node->set('field_peso', $form_state->getValue('peso'));
        $node->set('field_stock', $form_state->getValue('stock'));
        $node->set('field_fecha_vencimiento', $form_state->getValue('vencimiento'));

        // Update the 'Vivere'.
        $node->save();

      }
    }

    */
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
